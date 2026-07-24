<?php

declare(strict_types=1);

final class Request
{
    /**
     * HTTP Method
     */
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * All Request Data
     */
    public static function all(): array
    {
        return array_merge(
            $_GET,
            $_POST,
            self::json()
        );
    }

    /**
     * Single Input
     */
    public static function input(string $key, mixed $default = null): mixed
    {
        $data = self::all();

        return $data[$key] ?? $default;
    }

    /**
     * Query Parameter
     */
    public static function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Only Selected Keys
     */
    public static function only(array $keys): array
    {
        $result = [];

        foreach ($keys as $key) {
            $value = self::query($key);

            if ($value !== null && $value !== '') {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Uploaded File
     */
    public static function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * Request Header
     */
    public static function header(string $key): ?string
    {
        $headers = getallheaders();

        return $headers[$key] ?? null;
    }

    /**
     * JSON Body
     */
    public static function json(): array
    {
        $content = file_get_contents('php://input');

        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    public static function isGet(): bool
    {
        return self::method() === 'GET';
    }

    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    public static function isPut(): bool
    {
        return self::method() === 'PUT';
    }

    public static function isDelete(): bool
    {
        return self::method() === 'DELETE';
    }

    public static function isPatch(): bool
    {
        return self::method() === 'PATCH';
    }
}