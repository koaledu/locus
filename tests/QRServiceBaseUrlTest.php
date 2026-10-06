<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

use App\Services\QRService;
use PHPUnit\Framework\TestCase;

class QRServiceBaseUrlTest extends TestCase
{
    private array $serverBackup;
    private string|false $appUrlBackup;

    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;
        $this->appUrlBackup = getenv('APP_URL');
        unset($_SERVER['HTTP_HOST'], $_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO']);
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
        if ($this->appUrlBackup === false) {
            putenv('APP_URL');
        } else {
            putenv('APP_URL=' . $this->appUrlBackup);
        }
    }

    private function detect(?string $appUrl): string
    {
        putenv('APP_URL=' . ($appUrl ?? ''));
        return QRService::detectBaseUrl();
    }

    public function testConfiguredUrlBeatsLocalhostRequestHost(): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost:8080';

        $this->assertSame(
            'http://192.168.1.17:8080',
            $this->detect('http://192.168.1.17:8080')
        );
    }

    public function testLoopbackConfiguredUrlIsIgnored(): void
    {
        $_SERVER['HTTP_HOST'] = '192.168.1.17:8080';

        $this->assertSame('http://192.168.1.17:8080', $this->detect('http://localhost:8080'));
        $this->assertSame('http://192.168.1.17:8080', $this->detect('http://127.0.0.1:8080'));
        $this->assertSame('http://192.168.1.17:8080', $this->detect('http://[::1]:8080'));
    }

    public function testFallsBackToRequestHost(): void
    {
        $_SERVER['HTTP_HOST'] = '192.168.1.17:8080';

        $this->assertSame('http://192.168.1.17:8080', $this->detect(null));
    }

    public function testTunnelSchemeComesFromForwardedProto(): void
    {
        $_SERVER['HTTP_HOST'] = 'x.trycloudflare.com';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

        $this->assertSame('https://x.trycloudflare.com', $this->detect(null));
    }

    public function testLocalTlsIsPreserved(): void
    {
        $_SERVER['HTTP_HOST'] = 'locus.local';
        $_SERVER['HTTPS'] = 'on';

        $this->assertSame('https://locus.local', $this->detect(null));
    }

    public function testConfiguredUrlLosesTrailingSlash(): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost:8080';

        $this->assertSame('http://192.168.1.17:8080', $this->detect('http://192.168.1.17:8080/'));
    }

    public function testNeverReturnsEmptyUrl(): void
    {
        $url = $this->detect(null);

        $this->assertNotSame('', $url);
        $this->assertStringStartsWith('http', $url);
    }
}