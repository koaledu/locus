<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

namespace App\Services;

use App\Models\User;

class AuthService
{
    private JWTService $jwt;

    public function __construct()
    {
        $this->jwt = new JWTService();
    }

    public function register(array $data): array
    {
        $existing = User::findByEmail($data['email']);

        if ($existing) {
            return ['success' => false, 'error' => 'El correo ya está registrado'];
        }

        if (!in_array($data['role'], ['teacher', 'student'])) {
            return ['success' => false, 'error' => 'Rol inválido'];
        }

        $userId = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role' => $data['role'],
            'group' => $data['group'] ?? null,
            'dni' => $data['dni'] ?? null,
        ]);

        $token = $this->jwt->encode(['user_id' => $userId, 'role' => $data['role']]);
        $refreshToken = $this->jwt->generateRefreshToken($userId);

        return [
            'success' => true,
            'user' => [
                'id' => $userId,
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'group' => $data['group'] ?? null,
            ],
            'token' => $token,
            'refresh_token' => $refreshToken,
        ];
    }

    public function login(string $email, string $password): array
    {
        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Credenciales inválidas'];
        }

        $token = $this->jwt->encode(['user_id' => $user['id'], 'role' => $user['role']]);
        $refreshToken = $this->jwt->generateRefreshToken($user['id']);

        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'group' => $user['group'] ?? null,
            ],
            'token' => $token,
            'refresh_token' => $refreshToken,
        ];
    }

    public function refreshToken(string $refreshToken): ?array
    {
        $hashed = hash('sha256', $refreshToken);
        $record = \App\Helpers\Database::fetchOne(
            'SELECT user_id, expires_at FROM refresh_tokens WHERE token = ?',
            [$hashed]
        );

        if (!$record || strtotime($record['expires_at']) < time()) {
            return null;
        }

        $user = User::find($record['user_id']);
        if (!$user) return null;

        \App\Helpers\Database::query('DELETE FROM refresh_tokens WHERE token = ?', [$hashed]);

        $token = $this->jwt->encode(['user_id' => $user['id'], 'role' => $user['role']]);
        $newRefresh = $this->jwt->generateRefreshToken($user['id']);

        return [
            'token' => $token,
            'refresh_token' => $newRefresh,
        ];
    }

    public function logout(string $refreshToken): void
    {
        $hashed = hash('sha256', $refreshToken);
        \App\Helpers\Database::query('DELETE FROM refresh_tokens WHERE token = ?', [$hashed]);
    }

    public static function getAuthenticatedUser(): ?array
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? $_SERVER['Authorization']
            ?? '';
        if (empty($authHeader) && function_exists('getallheaders')) {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }

        if (empty($authHeader) && !empty($_COOKIE['token'])) {
            $authHeader = 'Bearer ' . $_COOKIE['token'];
        }

        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return null;
        }

        $jwt = new JWTService();
        $payload = $jwt->decode($matches[1]);
        if (!$payload) return null;

        $user = User::find($payload['user_id']);
        if (!$user) return null;

        unset($user['password_hash']);
        return $user;
    }
}
