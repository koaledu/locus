<?php

// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
//
// SPDX-License-Identifier: MIT

declare(strict_types=1);

use App\Services\JWTService;
use PHPUnit\Framework\TestCase;

class JWTServiceTest extends TestCase
{
    private JWTService $jwt;

    protected function setUp(): void
    {
        $this->jwt = new JWTService();
    }

    public function testEncodeAndDecode(): void
    {
        $payload = ['user_id' => 1, 'role' => 'teacher'];
        $token = $this->jwt->encode($payload);

        $decoded = $this->jwt->decode($token);
        $this->assertNotNull($decoded);
        $this->assertEquals(1, $decoded['user_id']);
        $this->assertEquals('teacher', $decoded['role']);
    }

    public function testInvalidToken(): void
    {
        $decoded = $this->jwt->decode('invalid.token.here');
        $this->assertNull($decoded);
    }

    public function testTamperedToken(): void
    {
        $payload = ['user_id' => 1, 'role' => 'student'];
        $token = $this->jwt->encode($payload);

        $parts = explode('.', $token);
        $parts[2] = 'invalidsignature';
        $tampered = implode('.', $parts);

        $decoded = $this->jwt->decode($tampered);
        $this->assertNull($decoded);
    }

    public function testExpiredToken(): void
    {
        $token = $this->jwt->encode(['user_id' => 1, 'role' => 'student', 'exp' => time() - 3600]);

        $decoded = $this->jwt->decode($token);
        $this->assertNull($decoded);
    }
}
