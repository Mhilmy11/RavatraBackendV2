<?php

declare(strict_types=1);

final class Router
{
    /**
     * Registered Routes
     */
    private static array $routes = [];

    /**
     * Register Route
     */
    private static function register(string $method, string $uri, array $action): void
    {
        self::$routes[strtoupper($method)][trim($uri, '/')] = $action;
    }

    /**
     * GET Route
     */
    public static function get(string $uri, array $action): void
    {
        self::register('GET', $uri, $action);
    }

    /**
     * POST Route
     */
    public static function post(string $uri, array $action): void
    {
        self::register('POST', $uri, $action);
    }

    /**
     * PUT Route
     */
    public static function put(string $uri, array $action): void
    {
        self::register('PUT', $uri, $action);
    }

    /**
     * PATCH Route
     */
    public static function patch(string $uri, array $action): void
    {
        self::register('PATCH', $uri, $action);
    }

    /**
     * DELETE Route
     */
    public static function delete(string $uri, array $action): void
    {
        self::register('DELETE', $uri, $action);
    }

    /**
     * Dispatch Route
     */
    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        // Hilangkan prefix "api" jika ada
        if (str_starts_with($uri, 'api/')) {
            $uri = substr($uri, 4);
        }

        $routes = self::$routes[$method] ?? [];

        foreach ($routes as $route => $action) {

            $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {

                array_shift($matches);

                [$controller, $function] = $action;

                $instance = new $controller();

                call_user_func_array([$instance, $function], $matches);

                return;
            }
        }

        Response::error(
            'Route not found',
            HTTP_NOT_FOUND
        );
    }
}