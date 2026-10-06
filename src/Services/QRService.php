<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

namespace App\Services;

class QRService
{
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function detectBaseUrl(): string
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            // A Cloudflare Tunnel terminates TLS and forwards plain HTTP to
            // Apache, so HTTPS is unset; the tunnel reports the real scheme.
            $proto = strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? $_SERVER['HTTPS'] ?? ''));
            $scheme = in_array($proto, ['https', 'on', '1'], true) ? 'https' : 'http';
            return "$scheme://{$_SERVER['HTTP_HOST']}";
        }
        $config = require __DIR__ . '/../../config/app.php';
        return rtrim($config['url'], '/');
    }

    public static function getQRCodeData(string $token, int $sessionId): string
    {
        $baseUrl = self::detectBaseUrl();
        return "$baseUrl/api/attendance/scan?token=$token&session=$sessionId";
    }
}
