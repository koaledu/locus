<?php

// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
//
// SPDX-License-Identifier: Apache-2.0

namespace App\Models;

use App\Helpers\Model;

class Attendance extends Model
{
    protected static string $table = 'attendance';
    protected static array $fillable = ['session_id', 'student_id', 'latitude', 'longitude', 'validated_by', 'ip_address', 'user_agent'];

    public static function findBySessionAndStudent(int $sessionId, int $studentId): ?array
    {
        return self::fetchOne(
            'SELECT id FROM attendance WHERE session_id = ? AND student_id = ?',
            [$sessionId, $studentId]
        );
    }

    public static function studentHistory(int $studentId): array
    {
        return self::fetchAll(
            'SELECT a.*, s.title as session_title, s.created_at as session_date, c.name as classroom_name
             FROM attendance a
             JOIN sessions s ON a.session_id = s.id
             LEFT JOIN classrooms c ON s.classroom_id = c.id
             WHERE a.student_id = ?
             ORDER BY a.created_at DESC
             LIMIT 50',
            [$studentId]
        );
    }

    public static function sessionAttendance(int $sessionId): array
    {
        return self::fetchAll(
            'SELECT a.*, u.name as student_name, u.email as student_email, u.dni as student_dni
             FROM attendance a
             JOIN users u ON a.student_id = u.id
             WHERE a.session_id = ?
             ORDER BY a.created_at ASC',
            [$sessionId]
        );
    }

    public static function sessionAttendanceWithStudents(int $sessionId, ?string $group): array
    {
        return self::fetchAll(
            'SELECT u.id, u.name, u.email, u.dni, u.`group`,
                    a.id as attendance_id, a.created_at as attended_at, a.validated_by, a.ip_address
             FROM users u
             LEFT JOIN attendance a ON a.session_id = ? AND a.student_id = u.id
             WHERE u.role = "student"
               AND (? IS NULL OR u.`group` = ?)
             ORDER BY u.name ASC',
            [$sessionId, $group, $group]
        );
    }

    public static function sessionAttendanceCSV(int $sessionId): array
    {
        return self::fetchAll(
            'SELECT a.created_at as hora, u.dni, u.name as estudiante, u.email,
                    a.validated_by as validacion, a.latitude, a.longitude, a.ip_address
             FROM attendance a
             JOIN users u ON a.student_id = u.id
             WHERE a.session_id = ?
             ORDER BY a.created_at ASC',
            [$sessionId]
        );
    }

    public static function countBySession(int $sessionId): int
    {
        $result = self::fetchOne(
            'SELECT COUNT(*) as total FROM attendance WHERE session_id = ?',
            [$sessionId]
        );
        return $result['total'] ?? 0;
    }
}
