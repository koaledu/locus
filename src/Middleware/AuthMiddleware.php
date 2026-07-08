<?php

// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
//
// SPDX-License-Identifier: MIT

namespace App\Middleware;

use App\Helpers\Router;
use App\Services\AuthService;

class AuthMiddleware
{
    public static function handle(): ?array
    {
        $user = AuthService::getAuthenticatedUser();
        if (!$user) {
            Router::sendJson(401, ['error' => 'No autorizado']);
            return null;
        }
        return $user;
    }

    public static function requireRole(string $role): callable
    {
        return function () use ($role) {
            $user = AuthService::getAuthenticatedUser();
            if (!$user) {
                Router::sendJson(401, ['error' => 'No autorizado']);
                exit;
            }
            if ($user['role'] !== $role) {
                Router::sendJson(403, ['error' => 'Acceso denegado para este rol']);
                exit;
            }
        };
    }
}
