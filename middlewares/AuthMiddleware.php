<?php

declare(strict_types=1);

final class AuthMiddleware
{
    public static function handle(): void
    {
        if (!isset($_SESSION['user_code'])) {

            Response::error(
                'Unauthorized.',
                HTTP_UNAUTHORIZED
            );
        }

        if (!isset($_SESSION['expired_at'])) {

            session_destroy();

            Response::error(
                'Session expired.',
                HTTP_UNAUTHORIZED
            );
        }

        if (time() > $_SESSION['expired_at']) {

            session_destroy();

            Response::error(
                'Session expired.',
                HTTP_UNAUTHORIZED
            );
        }
    }

    public static function userCode(): string
    {
        return $_SESSION['user_code'];
    }

    public static function role(): string
    {
        return $_SESSION['role'];
    }
}