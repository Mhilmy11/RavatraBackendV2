<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../core/Response.php';

class UserController
{
    public function store()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        $email = $input['email'] ?? null;
        $phone = $input['phone'] ?? null;

        if (!$email || !$phone) {
            return Response::json([
                'status' => 'error',
                'message' => 'Email dan phone wajib diisi'
            ], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return Response::json([
                'status' => 'error',
                'message' => 'Format email tidak valid'
            ], 400);
        }

        $model = new UserModel();

        $existingUser = $model->findByEmailPhone($email, $phone);

        if ($existingUser) {
            return Response::json([
                'status' => 'success',
                'message' => 'User sudah terdaftar',
                'data' => $existingUser,
                'is_existing' => true
            ]);
        }

        $requiredFields = ['firstname', 'lastname', 'company'];

        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                return Response::json([
                    'status' => 'error',
                    'message' => "$field wajib diisi"
                ], 400);
            }
        }

        $userId = $model->create($input);

        return Response::json([
            'status' => 'success',
            'message' => 'User berhasil dibuat',
            'user_id' => $userId,
            'is_existing' => false
        ]);
    }

    public function index()
    {
        $model = new UserModel();
        $data = $model->getAllUsers();

        return Response::json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}