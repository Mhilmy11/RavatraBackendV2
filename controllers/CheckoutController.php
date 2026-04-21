<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/TransactionModel.php';
require_once __DIR__ . '/../core/Response.php';

class CheckoutController
{
    public function store()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        if (
            empty($input['product_id']) ||
            empty($input['sales_id']) ||
            empty($input['firstname']) ||
            empty($input['email']) ||
            empty($input['phone'])
        ) {
            return Response::json([
                'status' => 'error',
                'message' => 'Field wajib belum lengkap'
            ], 400);
        }

        $userModel = new UserModel();
        $productModel = new ProductModel();
        $transactionModel = new TransactionModel();

        $user = $userModel->findByEmailOrPhone(
            $input['email'],
            $input['phone']
        );

        if (!$user) {
            $userId = $userModel->create([
                'firstname' => $input['firstname'],
                'lastname' => $input['lastname'] ?? '',
                'email' => $input['email'],
                'phone' => $input['phone'],
                'company' => $input['company'] ?? ''
            ]);
        } else {
            $userId = $user['id'];
        }

        $product = $productModel->findById($input['product_id']);

        if (!$product) {
            return Response::json([
                'status' => 'error',
                'message' => 'Product tidak ditemukan'
            ], 404);
        }

        $price = (int) $product['product_price'];

        // $id = "ORD" . time() . rand(100, 999);

        $uniqueCode = rand(100, 999);

        $totalAmount = $price + $uniqueCode;

        $expiredAt = date('Y-m-d H:i:s', strtotime('+1 day'));

        $transactionId = $transactionModel->create([
            'user_id' => $userId,
            'product_id' => $product['id'],
            'sales_id' => $input['sales_id'],
            'product_name' => $product['product_name'],
            'product_type' => $product['product_type'],
            'product_price' => $price,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'expired_at' => $expiredAt
        ]);

        return Response::json([
            'status' => 'success',
            'data' => [
                'transaction_id' => $transactionId,
                'expired_at' => $expiredAt,
                'payment' => [
                    'product_price' => $price,
                    'unique_code' => $uniqueCode,
                    'total_amount' => $totalAmount
                ],
                'product' => [
                    'id' => $product['id'],
                    'name' => $product['product_name'],
                    'location' => $product['location']
                ]
            ]
        ]);
    }
}