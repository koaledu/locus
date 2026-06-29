<?php

namespace App\Controllers;

use App\Helpers\Router;
use App\Models\Attendance;
use App\Services\AuthService;
use App\Services\LocationValidator;

class AttendanceController
{
    public function scan(): void
    {
        $token = $_GET['token'] ?? '';
        $sessionId = (int)($_GET['session'] ?? 0);

        if (empty($token) || $sessionId <= 0) {
            Router::render('student/scan', ['title' => 'Escanea QR', 'error' => 'QR inválido']);
            return;
        }

        $session = \App\Models\Session::findValidWithClassroom($sessionId, $token);

        if (!$session) {
            Router::render('student/scan', ['title' => 'Escanea QR', 'error' => 'Sesión expirada o QR inválido']);
            return;
        }

        $validationMode = $session['classroom_id'] === null ? 'none' : ($session['validation_mode'] ?? 'gps_or_network');

        Router::render('student/scan', [
            'title' => 'Registrar Asistencia',
            'session' => $session,
            'validation_mode' => $validationMode,
        ]);
    }

    public function register(): void
    {
        $data = Router::getJsonBody();

        $user = AuthService::getAuthenticatedUser();
        if (!$user) {
            Router::sendJson(401, ['error' => 'Debes iniciar sesión']);
            return;
        }

        if ($user['role'] !== 'student') {
            Router::sendJson(403, ['error' => 'Solo estudiantes pueden registrar asistencia']);
            return;
        }

        $session = \App\Models\Session::findValidWithClassroom($data['session_id'], $data['token']);

        if (!$session) {
            Router::sendJson(400, ['error' => 'Sesión expirada o QR inválido']);
            return;
        }

        $existing = Attendance::findBySessionAndStudent($session['id'], $user['id']);

        if ($existing) {
            Router::sendJson(409, ['error' => 'Ya registraste tu asistencia a esta sesión']);
            return;
        }

        $mode = $session['validation_mode'] ?? 'gps_or_network';
        $validatedBy = null;
        $gpsValid = false;
        $networkValid = false;

        if ($session['classroom_id'] === null) {
            $validatedBy = 'none';
        }

        $studentLat = $data['latitude'] ?? null;
        $studentLng = $data['longitude'] ?? null;

        if ($validatedBy === null && in_array($mode, ['gps_only', 'gps_or_network', 'gps_and_network'])) {
            if ($studentLat !== null && $studentLng !== null && $session['latitude'] && $session['longitude']) {
                $gpsValid = LocationValidator::validateGPS(
                    (float)$studentLat,
                    (float)$studentLng,
                    (float)$session['latitude'],
                    (float)$session['longitude'],
                    (int)$session['radius_meters']
                );
            }
        }

        if ($validatedBy === null && in_array($mode, ['network_only', 'gps_or_network', 'gps_and_network'])) {
            $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
            if ($session['ip_range']) {
                $ranges = array_map('trim', explode(',', $session['ip_range']));
                $networkValid = LocationValidator::validateNetwork($clientIp, $ranges);
            }

            if (!empty($data['ssid']) && !empty($session['ssid'])) {
                $ssidValid = LocationValidator::validateSSID($data['ssid'], $session['ssid']);
                $networkValid = $networkValid || $ssidValid;
            }
        }

        if ($validatedBy === null) {
            switch ($mode) {
                case 'gps_only':
                    if (!$gpsValid) {
                        Router::sendJson(403, ['error' => 'Debes estar dentro del aula (GPS)']);
                        return;
                    }
                    $validatedBy = 'gps';
                    break;

                case 'network_only':
                    if (!$networkValid) {
                        Router::sendJson(403, ['error' => 'Debes estar conectado a la red universitaria']);
                        return;
                    }
                    $validatedBy = 'network';
                    break;

                case 'gps_and_network':
                    if (!$gpsValid || !$networkValid) {
                        Router::sendJson(403, ['error' => 'Debes estar en el aula Y conectado a la red universitaria']);
                        return;
                    }
                    $validatedBy = 'both';
                    break;

                case 'gps_or_network':
                default:
                    if (!$gpsValid && !$networkValid) {
                        Router::sendJson(403, ['error' => 'Debes estar en el aula o conectado a la red universitaria']);
                        return;
                    }
                    $validatedBy = $gpsValid && $networkValid ? 'both' : ($gpsValid ? 'gps' : 'network');
                    break;
            }
        }

        Attendance::create([
            'session_id' => $session['id'],
            'student_id' => $user['id'],
            'latitude' => $studentLat,
            'longitude' => $studentLng,
            'validated_by' => $validatedBy,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        Router::sendJson(201, [
            'message' => 'Asistencia registrada exitosamente',
            'validated_by' => $validatedBy,
        ]);
    }

    public function history(): void
    {
        $user = AuthService::getAuthenticatedUser();
        if (!$user) {
            Router::sendJson(401, ['error' => 'No autorizado']);
            return;
        }

        $records = Attendance::studentHistory($user['id']);

        Router::sendJson(200, ['attendance' => $records]);
    }

    public function showHistory(): void
    {
        $user = AuthService::getAuthenticatedUser();
        if (!$user) {
            Router::redirect('/login');
            return;
        }
        Router::render('student/history', ['title' => 'Mi Historial', 'user' => $user]);
    }
}
