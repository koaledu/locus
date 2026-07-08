<?php

// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
//
// SPDX-License-Identifier: Apache-2.0

namespace App\Controllers;

use App\Helpers\Router;
use App\Models\Attendance;
use App\Models\Session;
use App\Models\User;
use App\Services\AuthService;

class ReportController
{
    public function list(): void
    {
        $user = AuthService::getAuthenticatedUser();
        if (!$user || $user['role'] !== 'teacher') {
            Router::sendJson(403, ['error' => 'No autorizado']);
            return;
        }

        $sessionId = $_GET['session_id'] ?? null;

        if ($sessionId) {
            $session = Session::findByIdAndTeacher((int)$sessionId, $user['id']);

            if (!$session) {
                Router::sendJson(404, ['error' => 'Sesión no encontrada']);
                return;
            }

            $attendance = Attendance::sessionAttendanceWithStudents((int)$sessionId, $session['group'] ?? null);
            $totalStudents = count($attendance);
            $totalPresent = count(array_filter($attendance, fn($a) => $a['attendance_id'] !== null));

            Router::sendJson(200, [
                'session' => $session,
                'students' => $attendance,
                'total_students' => $totalStudents,
                'total_present' => $totalPresent,
            ]);
        } else {
            $sessions = Session::teacherReportSessions($user['id']);

            Router::sendJson(200, ['sessions' => $sessions]);
        }
    }

    public function exportCSV(int $sessionId): void
    {
        $user = AuthService::getAuthenticatedUser();
        if (!$user || $user['role'] !== 'teacher') {
            Router::sendJson(403, ['error' => 'No autorizado']);
            return;
        }

        $session = Session::findByIdAndTeacher($sessionId, $user['id']);

        if (!$session) {
            Router::sendJson(404, ['error' => 'Sesión no encontrada']);
            return;
        }

        $attendance = Attendance::sessionAttendanceCSV($sessionId);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="asistencia_' . $session['id'] . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['Hora', 'DNI', 'Estudiante', 'Email', 'Validación', 'Latitud', 'Longitud', 'IP']);

        foreach ($attendance as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    public function showReports(): void
    {
        $user = AuthService::getAuthenticatedUser();
        if (!$user || $user['role'] !== 'teacher') {
            Router::redirect('/login');
            return;
        }
        Router::render('teacher/reports', ['title' => 'Reportes', 'user' => $user]);
    }
}
