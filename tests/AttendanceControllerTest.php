<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

use App\Controllers\AttendanceController;
use App\Services\QRService;
use PHPUnit\Framework\TestCase;

class AttendanceControllerTest extends TestCase
{
    private array $serverBackup;

    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;
        unset($_SERVER['HTTP_CF_CONNECTING_IP'], $_SERVER['REMOTE_ADDR'],
              $_SERVER['HTTP_HOST'], $_SERVER['HTTP_X_FORWARDED_PROTO'], $_SERVER['HTTPS']);
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
    }

    public function testClientIpPrefersCloudflareHeader(): void
    {
        $_SERVER['HTTP_CF_CONNECTING_IP'] = '192.168.10.55';
        $_SERVER['REMOTE_ADDR'] = '172.64.0.1';

        $this->assertSame('192.168.10.55', AttendanceController::clientIp());
    }

    public function testClientIpFallsBackToRemoteAddr(): void
    {
        $_SERVER['REMOTE_ADDR'] = '10.0.0.7';

        $this->assertSame('10.0.0.7', AttendanceController::clientIp());
    }

    public function testClientIpIsEmptyWithoutHeaders(): void
    {
        $this->assertSame('', AttendanceController::clientIp());
    }

    public function testBaseUrlUsesForwardedProtoFromTunnel(): void
    {
        $_SERVER['HTTP_HOST'] = 'locus.example.com';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

        $this->assertSame('https://locus.example.com', QRService::detectBaseUrl());
    }

    public function testBaseUrlUsesHttpsWhenTerminatedLocally(): void
    {
        $_SERVER['HTTP_HOST'] = 'locus.example.com';
        $_SERVER['HTTPS'] = 'on';

        $this->assertSame('https://locus.example.com', QRService::detectBaseUrl());
    }

    public function testBaseUrlDefaultsToHttp(): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost:8080';

        $this->assertSame('http://localhost:8080', QRService::detectBaseUrl());
    }
}