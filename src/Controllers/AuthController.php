<?php

namespace App\Controllers;

use App\Helpers\Router;
use App\Services\AuthService;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function showLogin(): void
    {
        $user = AuthService::getAuthenticatedUser();
        if ($user) {
            $route = $user['role'] === 'teacher' ? '/teacher/dashboard' : '/student/history';
            Router::redirect($route);
            return;
        }
        Router::render('auth/login', ['title' => 'Iniciar Sesión']);
    }

    public function showRegister(): void
    {
        $user = AuthService::getAuthenticatedUser();
        if ($user) {
            $route = $user['role'] === 'teacher' ? '/teacher/dashboard' : '/student/history';
            Router::redirect($route);
            return;
        }
        Router::render('auth/register', ['title' => 'Registrarse']);
    }

    public function login(): void
    {
        $data = Router::getJsonBody();

        if (empty($data['email']) || empty($data['password'])) {
            Router::sendJson(400, ['error' => 'Email y contraseña requeridos']);
            return;
        }

        $result = $this->authService->login($data['email'], $data['password']);

        if (!$result['success']) {
            Router::sendJson(401, $result);
            return;
        }

        Router::sendJson(200, $result);
    }

    public function register(): void
    {
        $data = Router::getJsonBody();

        $required = ['name', 'email', 'password', 'role'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Router::sendJson(400, ['error' => "Campo requerido: $field"]);
                return;
            }
        }

        if (strlen($data['password']) < 6) {
            Router::sendJson(400, ['error' => 'La contraseña debe tener al menos 6 caracteres']);
            return;
        }

        $result = $this->authService->register($data);

        if (!$result['success']) {
            Router::sendJson(409, $result);
            return;
        }

        Router::sendJson(201, $result);
    }

    public function refresh(): void
    {
        $data = Router::getJsonBody();

        if (empty($data['refresh_token'])) {
            Router::sendJson(400, ['error' => 'Refresh token requerido']);
            return;
        }

        $result = $this->authService->refreshToken($data['refresh_token']);

        if (!$result) {
            Router::sendJson(401, ['error' => 'Refresh token inválido o expirado']);
            return;
        }

        Router::sendJson(200, $result);
    }

    public function logout(): void
    {
        $data = Router::getJsonBody();

        if (!empty($data['refresh_token'])) {
            $this->authService->logout($data['refresh_token']);
        }

        Router::sendJson(200, ['message' => 'Sesión cerrada exitosamente']);
    }

    public function me(): void
    {
        $user = AuthService::getAuthenticatedUser();
        if (!$user) {
            Router::sendJson(401, ['error' => 'No autorizado']);
            return;
        }
        Router::sendJson(200, ['user' => $user]);
    }
}
