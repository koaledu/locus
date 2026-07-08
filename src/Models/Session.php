<?php

// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
//
// SPDX-License-Identifier: Apache-2.0

namespace App\Models;

use App\Helpers\Database;
use App\Helpers\Model;

class Session extends Model
{
    protected static string $table = 'sessions';
    protected static array $fillable = ['teacher_id', 'classroom_id', 'group', 'title', 'qr_token', 'expires_at', 'is_active'];

    public static function findWithClassroom(int $id): ?array
    {
        return self::fetchOne(
            'SELECT s.*, c.name as classroom_name, c.latitude, c.longitude, c.radius_meters, c.ssid, c.ip_range,
                    gc.validation_mode
             FROM sessions s
             LEFT JOIN classrooms c ON s.classroom_id = c.id
             LEFT JOIN geofence_config gc ON c.id = gc.classroom_id
             WHERE s.id = ?',
            [$id]
        );
    }

    public static function findValidWithClassroom(int $id, string $token): ?array
    {
        return self::fetchOne(
            'SELECT s.*, c.latitude, c.longitude, c.radius_meters, c.ssid, c.ip_range,
                    gc.validation_mode
             FROM sessions s
             LEFT JOIN classrooms c ON s.classroom_id = c.id
             LEFT JOIN geofence_config gc ON c.id = gc.classroom_id
             WHERE s.id = ? AND s.qr_token = ? AND s.is_active = 1 AND s.expires_at > NOW()',
            [$id, $token]
        );
    }

    public static function teacherSessions(int $teacherId): array
    {
        return self::fetchAll(
            'SELECT s.*, c.name as classroom_name,
                    (SELECT COUNT(*) FROM attendance WHERE session_id = s.id) as total_present,
                    (SELECT COUNT(*) FROM users WHERE role = "student"
                     AND (s.`group` IS NULL OR s.`group` = "" OR `group` = s.`group`)) as total_students
             FROM sessions s
             LEFT JOIN classrooms c ON s.classroom_id = c.id
             WHERE s.teacher_id = ?
             ORDER BY s.created_at DESC',
            [$teacherId]
        );
    }

    public static function activeSessions(int $studentId): array
    {
        return self::fetchAll(
            'SELECT DISTINCT s.*, c.name as classroom_name,
                    (SELECT COUNT(*) FROM attendance WHERE session_id = s.id AND student_id = ?) as marked
             FROM sessions s
             LEFT JOIN classrooms c ON s.classroom_id = c.id
             WHERE s.is_active = 1 AND s.expires_at > NOW()
             ORDER BY s.created_at DESC',
            [$studentId]
        );
    }

    public static function close(int $id, int $teacherId): int
    {
        return Database::update(
            static::$table,
            ['is_active' => 0, 'expires_at' => date('Y-m-d H:i:s')],
            'id = ? AND teacher_id = ?',
            [$id, $teacherId]
        );
    }

    public static function teacherReportSessions(int $teacherId): array
    {
        return self::fetchAll(
            'SELECT s.*,
                    (SELECT COUNT(*) FROM attendance WHERE session_id = s.id) as total_present,
                    (SELECT COUNT(*) FROM users WHERE role = "student"
                     AND (s.`group` IS NULL OR s.`group` = "" OR `group` = s.`group`)) as total_students
             FROM sessions s
             WHERE s.teacher_id = ?
             ORDER BY s.created_at DESC',
            [$teacherId]
        );
    }

    public static function findByIdAndTeacher(int $id, int $teacherId): ?array
    {
        return self::fetchOne(
            'SELECT * FROM sessions WHERE id = ? AND teacher_id = ?',
            [$id, $teacherId]
        );
    }
}
