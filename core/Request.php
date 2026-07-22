<?php

declare(strict_types=1);

final class Request
{
    /**
     * Get HTTP Method
     */
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Get All Input
     */
    public function all(): array
    {
        return array_merge(
            $_GET,
            $_POST,
            $this->json()
        );
    }

    /**
     * Get Input Value
     */
    public function input(string $key, mixed $default = null): mixed
    {
        $data = $this->all();

        return $data[$key] ?? $default;
    }

    /**
     * Get Query Parameter
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Get Uploaded File
     */
    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * Get Request Header
     */
    public function header(string $key): ?string
    {
        $headers = getallheaders();

        return $headers[$key] ?? null;
    }

    /**
     * Get JSON Body
     */
    public function json(): array
    {
        $content = file_get_contents('php://input');

        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Check POST Request
     */
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     * Check GET Request
     */
    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }
}