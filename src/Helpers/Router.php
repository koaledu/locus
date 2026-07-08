<?php

// SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero, Eduardo Monsalve Ariza, Jesús Manuel Farfán
//
// SPDX-License-Identifier: Apache-2.0

namespace App\Helpers;

class Router
{
    private array $routes = [];
    private array $middleware = [];

    public function get(string $path, callable $handler, array $middleware = []): void
    {
        $this->routes['GET'][$path] = ['handler' => $handler, 'middleware' => $middleware];
    }

    public function post(string $path, callable $handler, array $middleware = []): void
    {
        $this->routes['POST'][$path] = ['handler' => $handler, 'middleware' => $middleware];
    }

    public function put(string $path, callable $handler, array $middleware = []): void
    {
        $this->routes['PUT'][$path] = ['handler' => $handler, 'middleware' => $middleware];
    }

    public function delete(string $path, callable $handler, array $middleware = []): void
    {
        $this->routes['DELETE'][$path] = ['handler' => $handler, 'middleware' => $middleware];
    }

    public function addMiddleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function dispatch(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->middleware as $mw) {
            call_user_func($mw);
        }

        if (!isset($this->routes[$method])) {
            $this->sendJson(405, ['error' => 'Method not allowed']);
            return;
        }

        foreach ($this->routes[$method] as $path => $route) {
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                foreach ($route['middleware'] as $mw) {
                    $result = call_user_func($mw);
                    if ($result !== null) {
                        return;
                    }
                }

                call_user_func($route['handler'], $params);
                return;
            }
        }

        $this->sendJson(404, ['error' => 'Not found']);
    }

    public static function sendJson(int $status, mixed $data): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    public static function render(string $view, array $data = []): void
    {
        header('Content-Type: text/html; charset=utf-8');
        extract($data);
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: $view");
        }
        require __DIR__ . '/../../views/layouts/main.php';
    }

    public static function renderPartial(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: $view");
        }
        require $viewPath;
    }

    public static function getJsonBody(): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        return $data ?: [];
    }
}
