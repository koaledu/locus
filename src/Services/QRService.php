<?php

namespace App\Services;

class QRService
{
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function getQRCodeData(string $token, int $sessionId): string
    {
        $config = require __DIR__ . '/../../config/app.php';
        $baseUrl = rtrim($config['url'], '/');
        return "$baseUrl/api/attendance/scan?token=$token&session=$sessionId";
    }

    public static function generatePNG(string $data): string
    {
        $encoded = urlencode($data);
        return "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=$encoded";
    }
}
