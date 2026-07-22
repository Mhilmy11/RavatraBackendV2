<?php

declare(strict_types=1);

final class Response
{
    /**
     * Success Response
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $status = HTTP_OK,
        mixed $meta = null
    ): never {
        http_response_code($status);

        header('Content-Type: application/json');

        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    /**
     * Error Response
     */
    public static function error(
        string $message = 'Error',
        int $status = HTTP_BAD_REQUEST,
        mixed $errors = null
    ): never {
        http_response_code($status);

        header('Content-Type: application/json');

        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}