<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

namespace App\Controllers;

use App\Helpers\Router;
use App\Models\Attendance;
use App\Services\AuthService;
use App\Services\LocationValidator;

class AttendanceController
{
    // Behind a Cloudflare Tunnel REMOTE_ADDR is the edge IP, so prefer the
    // real client IP Cloudflare sets. Only safe because the tunnel is the
    // sole ingress; X-Forwarded-For is client-spoofable and is ignored.
    public static function clientIp(): string
    {
        return $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    }

    public function scan(): void
    {
        $token = $_GET['token'] ?? '';
        $sessionId = (int)($_GET['session'] ?? 0);

        if (empty($token) || $sessionId <= 0) {
            Router::render('student/scan', ['title' => 'Escanea QR', 'error' => 'QR inválido']);
            return;
        }

        $session = \App\Models\Session::findValidWithLocation($sessionId, $token);

        if (!$session) {
            Router::render('student/scan', ['title' => 'Escanea QR', 'error' => 'Sesión expirada o QR inválido']);
            return;
        }

        Router::render('student/scan', [
            'title' => 'Registrar asistencia',
            'session' => $session,
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
            Router::sendJson(403, ['error' => 'Solo participantes pueden registrar asistencia']);
            return;
        }

        $session = \App\Models\Session::findValidWithLocation($data['session_id'], $data['token']);

        if (!$session) {
            Router::sendJson(400, ['error' => 'Sesión expirada o QR inválido']);
            return;
        }

        $existing = Attendance::findBySessionAndStudent($session['id'], $user['id']);

        if ($existing) {
            Router::sendJson(409, ['error' => 'Ya registraste tu asistencia a esta sesión']);
            return;
        }

        $studentLat = $data['latitude'] ?? null;
        $studentLng = $data['longitude'] ?? null;
        $validatedBy = 'none';

        // A session without a location is QR-only: nothing to check.
        if ($session['location_id'] !== null) {
            $gpsValid = $studentLat !== null && $studentLng !== null
                && LocationValidator::validateGPS(
                    (float)$studentLat,
                    (float)$studentLng,
                    (float)$session['latitude'],
                    (float)$session['longitude'],
                    (int)$session['radius_meters']
                );

            if (!$gpsValid) {
                Router::sendJson(403, ['error' => 'Debes estar dentro de la ubicación']);
                return;
            }
            $validatedBy = 'gps';
        }

        Attendance::create([
            'session_id' => $session['id'],
            'student_id' => $user['id'],
            'latitude' => $studentLat,
            'longitude' => $studentLng,
            'validated_by' => $validatedBy,
            'ip_address' => self::clientIp(),
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
        Router::render('student/history', ['title' => 'Mi historial', 'user' => $user]);
    }
}
