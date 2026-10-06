<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

namespace App\Controllers;

use App\Helpers\Database;
use App\Helpers\Router;
use App\Services\AuthService;

class LocationController
{
    public function show(): void
    {
        $user = AuthService::getAuthenticatedUser();
        if (!$user || $user['role'] !== 'organizer') {
            Router::redirect('/login');
            return;
        }

Router::render('organizer/locations', [
            'title' => 'Ubicaciones',
            'user' => $user,
            'locations' => Database::fetchAll('SELECT * FROM locations ORDER BY name'),
        ]);
    }

    public function create(): void
    {
        $user = AuthService::getAuthenticatedUser();
        if (!$user || $user['role'] !== 'organizer') {
            Router::sendJson(403, ['error' => 'Solo organizadores pueden crear ubicaciones']);
            return;
        }

        $data = Router::getJsonBody();

        $error = self::validate($data);
        if ($error) {
            Router::sendJson(400, ['error' => $error]);
            return;
        }

        $id = Database::insert('locations', [
            'name' => trim($data['name']),
            'latitude' => (float)$data['latitude'],
            'longitude' => (float)$data['longitude'],
            'radius_meters' => (int)($data['radius_meters'] ?? 50),
        ]);

        Router::sendJson(201, ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $user = AuthService::getAuthenticatedUser();
        if (!$user || $user['role'] !== 'organizer') {
            Router::sendJson(403, ['error' => 'No autorizado']);
            return;
        }

        Database::query('DELETE FROM locations WHERE id = ?', [$id]);

        Router::sendJson(200, ['message' => 'Ubicación eliminada']);
    }

    public static function validate(array $data): ?string
    {
        if (trim((string)($data['name'] ?? '')) === '') {
            return 'Nombre requerido';
        }

        foreach (['latitude' => 90, 'longitude' => 180] as $field => $max) {
            if (!is_numeric($data[$field] ?? null) || abs((float)$data[$field]) > $max) {
                return "Coordenada inválida: $field";
            }
        }

        $radius = $data['radius_meters'] ?? 50;
        if (!is_numeric($radius) || (int)$radius < 1) {
            return 'Radio inválido';
        }

        return null;
    }
}