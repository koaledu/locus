<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

namespace App\Controllers;

use App\Helpers\Router;
use App\Models\Session;
use App\Services\AuthService;
use App\Services\QRService;

class SessionController
{
    public function showCreate(): void
    {
        $user = AuthService::getAuthenticatedUser();
        if (!$user || $user['role'] !== 'teacher') {
            Router::redirect('/login');
            return;
        }

        $locations = \App\Helpers\Database::fetchAll('SELECT * FROM locations ORDER BY name');
        Router::render('teacher/create-session', [
            'title' => 'Crear sesión',
            'user' => $user,
            'locations' => $locations,
        ]);
    }

    public function create(): void
    {
        $user = AuthService::getAuthenticatedUser();
        if (!$user || $user['role'] !== 'teacher') {
            Router::sendJson(403, ['error' => 'Solo organizadores pueden crear sesiones']);
            return;
        }

        $data = Router::getJsonBody();
        $config = require __DIR__ . '/../../config/app.php';

        $token = QRService::generateToken();

        if (!empty($data['expires_at'])) {
            $expiresAt = date('Y-m-d') . ' ' . $data['expires_at'] . ':00';
        } else {
            $expiryMinutes = $data['expiry_minutes'] ?? $config['qr_expiry_minutes'];
            $expiresAt = date('Y-m-d H:i:s', time() + ($expiryMinutes * 60));
        }

        $sessionId = Session::create([
            'teacher_id' => $user['id'],
            'location_id' => $data['location_id'] ?? null,
            'group' => $user['group'] ?? null,
            'title' => $data['title'] ?? 'Sesión sin título',
            'qr_token' => $token,
            'expires_at' => $expiresAt,
        ]);

        $qrData = QRService::getQRCodeData($token, $sessionId);

        Router::sendJson(201, [
            'session' => [
                'id' => $sessionId,
                'title' => $data['title'] ?? 'Sesión sin título',
                'token' => $token,
                'expires_at' => $expiresAt,
                'qr_data' => $qrData,
                'group' => $user['group'] ?? null,
            ],
        ]);
    }

    public function list(): void
    {
        $user = AuthService::getAuthenticatedUser();
        if (!$user) {
            Router::sendJson(401, ['error' => 'No autorizado']);
            return;
        }

        if ($user['role'] === 'teacher') {
            $sessions = Session::teacherSessions($user['id']);
        } else {
            $sessions = Session::activeSessions($user['id']);
        }

        Router::sendJson(200, ['sessions' => $sessions]);
    }

    public function get(int $id): void
    {
        $session = Session::findWithLocation($id);

        if (!$session) {
            Router::sendJson(404, ['error' => 'Sesión no encontrada']);
            return;
        }

        $qrData = QRService::getQRCodeData($session['qr_token'], $session['id']);

        Router::sendJson(200, [
            'session' => $session,
            'qr_data' => $qrData,
        ]);
    }

    public function close(int $id): void
    {
        $user = AuthService::getAuthenticatedUser();
        if (!$user || $user['role'] !== 'teacher') {
            Router::sendJson(403, ['error' => 'No autorizado']);
            return;
        }

        Session::close($id, $user['id']);

        Router::sendJson(200, ['message' => 'Sesión cerrada']);
    }

    public function showDashboard(): void
    {
        $user = AuthService::getAuthenticatedUser();
        if (!$user) {
            Router::redirect('/login');
            return;
        }
        Router::render('teacher/dashboard', ['title' => 'Panel del organizador', 'user' => $user]);
    }
}
