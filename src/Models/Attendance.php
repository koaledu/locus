<?php

// SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
// SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero
//
// SPDX-License-Identifier: Apache-2.0

namespace App\Models;

use App\Helpers\Model;

class Attendance extends Model
{
    protected static string $table = 'attendance';

    public static function findBySessionAndParticipant(int $sessionId, int $participantId): ?array
    {
        return self::fetchOne(
            'SELECT id FROM attendance WHERE session_id = ? AND participant_id = ?',
            [$sessionId, $participantId]
        );
    }

    public static function participantHistory(int $participantId): array
    {
        return self::fetchAll(
            'SELECT a.*, s.title as session_title, s.created_at as session_date, c.name as location_name
             FROM attendance a
             JOIN sessions s ON a.session_id = s.id
             LEFT JOIN locations c ON s.location_id = c.id
             WHERE a.participant_id = ?
             ORDER BY a.created_at DESC
             LIMIT 50',
            [$participantId]
        );
    }

    public static function sessionAttendanceWithParticipants(int $sessionId, ?string $group): array
    {
        return self::fetchAll(
            'SELECT u.id, u.name, u.email, u.document_id, u.`group`,
                    a.id as attendance_id, a.created_at as attended_at, a.validated_by
             FROM users u
             LEFT JOIN attendance a ON a.session_id = ? AND a.participant_id = u.id
             WHERE u.role = "participant"
               AND (? IS NULL OR u.`group` = ?)
             ORDER BY u.name ASC',
            [$sessionId, $group, $group]
        );
    }

    public static function sessionAttendanceCSV(int $sessionId): array
    {
        return self::fetchAll(
            'SELECT a.created_at as hora, u.document_id, u.name as participante, u.email,
                    a.validated_by as validacion, a.latitude, a.longitude
             FROM attendance a
             JOIN users u ON a.participant_id = u.id
             WHERE a.session_id = ?
             ORDER BY a.created_at ASC',
            [$sessionId]
        );
    }
}
