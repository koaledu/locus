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
        $config = require __DIR__ . '/../../config/app.php';
        $configured = rtrim((string)$config['url'], '/');

        // APP_URL wins when it names a real host: browsing through
        // localhost would otherwise bake localhost into every printed QR
        // code, which no phone can reach. A loopback value is ignored so
        // the default does not override the real request host.
        if ($configured !== '' && !self::isLoopbackUrl($configured)) {
            return $configured;
        }

        if (!empty($_SERVER['HTTP_HOST'])) {
            // A Cloudflare Tunnel terminates TLS and forwards plain HTTP to
            // Apache, so HTTPS is unset; the tunnel reports the real scheme.
            $proto = strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? $_SERVER['HTTPS'] ?? ''));
            $scheme = in_array($proto, ['https', 'on', '1'], true) ? 'https' : 'http';
            return "$scheme://{$_SERVER['HTTP_HOST']}";
        }

        return $configured !== '' ? $configured : 'http://localhost';
    }

    private static function isLoopbackUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        return $host === null
            || $host === 'localhost'
            || $host === '127.0.0.1'
            || $host === '::1'
            || $host === '[::1]';
    }

    public static function getQRCodeData(string $token, int $sessionId): string
    {
        $baseUrl = self::detectBaseUrl();
        return "$baseUrl/api/attendance/scan?token=$token&session=$sessionId";
    }
}
