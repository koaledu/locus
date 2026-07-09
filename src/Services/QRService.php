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
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
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

    public static function generatePNG(string $data): string
    {
        $encoded = urlencode($data);
        return "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=$encoded";
    }
}
