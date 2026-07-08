<?php

// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
//
// SPDX-License-Identifier: MIT

require_once __DIR__ . '/../src/Helpers/Env.php';

$config = require __DIR__ . '/../config/app.php';
date_default_timezone_set($config['timezone']);

spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../src/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Serve static files when using PHP built-in server
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

use App\Helpers\Router;
use App\Controllers\AuthController;
use App\Controllers\SessionController;
use App\Controllers\AttendanceController;
use App\Controllers\ReportController;

$router = new Router();

$router->get('/', function () {
    $user = \App\Services\AuthService::getAuthenticatedUser();
    if ($user) {
        $route = $user['role'] === 'teacher' ? '/teacher/dashboard' : '/student/history';
        header('Location: ' . $route);
        exit;
    }
    Router::render('auth/login', ['title' => 'Bienvenido']);
});

$router->get('/login', function () {
    (new AuthController())->showLogin();
});

$router->get('/register', function () {
    (new AuthController())->showRegister();
});

$router->get('/teacher/dashboard', function () {
    (new SessionController())->showDashboard();
});

$router->get('/teacher/session/create', function () {
    (new SessionController())->showCreate();
});

$router->get('/teacher/reports', function () {
    (new ReportController())->showReports();
});

$router->get('/student/history', function () {
    (new AttendanceController())->showHistory();
});

$router->get('/api/attendance/scan', function () {
    (new AttendanceController())->scan();
});

$router->post('/api/auth/login', function () {
    (new AuthController())->login();
});

$router->post('/api/auth/register', function () {
    (new AuthController())->register();
});

$router->post('/api/auth/refresh', function () {
    (new AuthController())->refresh();
});

$router->post('/api/auth/logout', function () {
    (new AuthController())->logout();
});

$router->get('/api/auth/me', function () {
    (new AuthController())->me();
});

$router->get('/api/sessions', function () {
    (new SessionController())->list();
});

$router->post('/api/sessions', function () {
    (new SessionController())->create();
});

$router->get('/api/sessions/{id}', function (array $params) {
    (new SessionController())->get((int)$params['id']);
});

$router->post('/api/sessions/{id}/close', function (array $params) {
    (new SessionController())->close((int)$params['id']);
});

$router->post('/api/attendance/register', function () {
    (new AttendanceController())->register();
});

$router->get('/api/attendance/history', function () {
    (new AttendanceController())->history();
});

$router->get('/api/reports', function () {
    (new ReportController())->list();
});

$router->get('/api/reports/{id}/csv', function (array $params) {
    (new ReportController())->exportCSV((int)$params['id']);
});

$router->dispatch();
