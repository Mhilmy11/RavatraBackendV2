<?php

require_once __DIR__ . '/../models/UserModel.php';

class AuthMiddleware
{
    public static function handle()
    {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            Response::json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ]);
            exit;
        }

        $token = str_replace("Bearer ", "", $headers['Authorization']);
        $decoded = base64_decode($token);
        $parts = explode("|", $decoded);

        if (count($parts) !== 2) {
            Response::json([
                'status' => 'error',
                'message' => 'Invalid token'
            ]);
            exit;
        }

        $userId = $parts[0];

        $model = new UserModel();
        $user = $model->findByEmail($userId);

        return $user;
    }
}