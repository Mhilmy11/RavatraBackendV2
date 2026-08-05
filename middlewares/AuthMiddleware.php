<?php

declare(strict_types=1);

final class AuthMiddleware
{
    /**
     * Check Authentication
     */
    public static function handle(): void
    {
        if (!isset($_SESSION['user_code'])) {

            Response::error(
                'Unauthorized.',
                HTTP_UNAUTHORIZED
            );
        }

        if (!isset($_SESSION['expired_at'])) {

            self::logout();

            Response::error(
                'Session expired.',
                HTTP_UNAUTHORIZED
            );
        }

        if (time() > (int) $_SESSION['expired_at']) {

            self::logout();

            Response::error(
                'Session expired.',
                HTTP_UNAUTHORIZED
            );
        }
    }

    /**
     * Get User ID
     */
    public static function userId(): int
    {
        return (int) $_SESSION['user_id'];
    }

    /**
     * Get User Code
     */
    public static function userCode(): string
    {
        return (string) $_SESSION['user_code'];
    }

    /**
     * Get User Firstname
     */
    public static function firstname(): string
    {
        return (string) $_SESSION['firstname'];
    }

    /**
     * Get User Role
     */
    public static function role(): string
    {
        return (string) $_SESSION['role'];
    }

    /**
     * Check Admin Role
     */
    public static function isAdmin(): bool
    {
        return self::role() === 'ADMIN';
    }

    /**
     * Destroy Session
     */
    public static function logout(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {

            if (ini_get('session.use_cookies')) {

                $params = session_get_cookie_params();

                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }

            session_destroy();
        }
    }
}