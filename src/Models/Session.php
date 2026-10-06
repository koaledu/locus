<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

namespace App\Models;

use App\Helpers\Database;
use App\Helpers\Model;

class Session extends Model
{
    protected static string $table = 'sessions';

    public static function findWithLocation(int $id): ?array
    {
        return self::fetchOne(
            'SELECT s.*, c.name as location_name, c.latitude, c.longitude, c.radius_meters
             FROM sessions s
             LEFT JOIN locations c ON s.location_id = c.id
             WHERE s.id = ?',
            [$id]
        );
    }

    public static function findValidWithLocation(int $id, string $token): ?array
    {
        return self::fetchOne(
            'SELECT s.*, c.latitude, c.longitude, c.radius_meters
             FROM sessions s
             LEFT JOIN locations c ON s.location_id = c.id
             WHERE s.id = ? AND s.qr_token = ? AND s.is_active = 1 AND s.expires_at > NOW()',
            [$id, $token]
        );
    }

    public static function organizerSessions(int $organizerId): array
    {
        return self::fetchAll(
            'SELECT s.*, c.name as location_name,
                    (SELECT COUNT(*) FROM attendance WHERE session_id = s.id) as total_present,
                    (SELECT COUNT(*) FROM users WHERE role = "participant"
                     AND (s.`group` IS NULL OR s.`group` = "" OR `group` = s.`group`)) as total_participants
             FROM sessions s
             LEFT JOIN locations c ON s.location_id = c.id
             WHERE s.organizer_id = ?
             ORDER BY s.created_at DESC',
            [$organizerId]
        );
    }

    public static function activeSessions(int $participantId): array
    {
        return self::fetchAll(
            'SELECT DISTINCT s.*, c.name as location_name,
                    (SELECT COUNT(*) FROM attendance WHERE session_id = s.id AND participant_id = ?) as marked
             FROM sessions s
             LEFT JOIN locations c ON s.location_id = c.id
             WHERE s.is_active = 1 AND s.expires_at > NOW()
             ORDER BY s.created_at DESC',
            [$participantId]
        );
    }

    public static function close(int $id, int $organizerId): int
    {
        return Database::update(
            static::$table,
            ['is_active' => 0, 'expires_at' => date('Y-m-d H:i:s')],
            'id = ? AND organizer_id = ?',
            [$id, $organizerId]
        );
    }

    public static function organizerReportSessions(int $organizerId): array
    {
        return self::fetchAll(
            'SELECT s.*,
                    (SELECT COUNT(*) FROM attendance WHERE session_id = s.id) as total_present,
                    (SELECT COUNT(*) FROM users WHERE role = "participant"
                     AND (s.`group` IS NULL OR s.`group` = "" OR `group` = s.`group`)) as total_participants
             FROM sessions s
             WHERE s.organizer_id = ?
             ORDER BY s.created_at DESC',
            [$organizerId]
        );
    }

    public static function findByIdAndOrganizer(int $id, int $organizerId): ?array
    {
        return self::fetchOne(
            'SELECT * FROM sessions WHERE id = ? AND organizer_id = ?',
            [$id, $organizerId]
        );
    }
}
