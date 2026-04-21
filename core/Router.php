<?php

class Router
{
    private $routes = [];

    public function get($path, $handler)
    {
        $this->routes[] = [
            'method' => 'GET',
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function post($path, $handler)
    {
        $this->routes[] = [
            'method' => 'POST',
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function put($path, $handler)
    {
        $this->routes[] = [
            'method' => 'PUT',
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function delete($path, $handler)
    {
        $this->routes[] = [
            'method' => 'DELETE',
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function run()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {

            // convert {id} jadi regex
            $pattern = preg_replace('#\{id\}#', '(\d+)', $route['path']);

            // cocokkan route
            if ($route['method'] === $method && preg_match("#^$pattern$#", $uri, $matches)) {

                array_shift($matches); // hapus full match

                return $this->execute($route['handler'], $matches);
            }
        }

        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Route not found'
        ]);
    }

    private function execute($handler, $params = [])
    {
        list($controller, $method) = explode('@', $handler);

        $controllerPath = __DIR__ . '/../controllers/' . $controller . '.php';

        if (!file_exists($controllerPath)) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Controller not found'
            ]);
            return;
        }

        require_once $controllerPath;

        if (!class_exists($controller)) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Controller class not found'
            ]);
            return;
        }

        $instance = new $controller();

        if (!method_exists($instance, $method)) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Method not found'
            ]);
            return;
        }

        return call_user_func_array([$instance, $method], $params);
    }
}