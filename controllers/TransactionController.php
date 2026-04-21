<?php

require_once __DIR__ . '/../models/TransactionModel.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../core/Response.php';

require_once __DIR__ . '/../services/InvoiceService.php';
require_once __DIR__ . '/../services/MailService.php';

class TransactionController
{
    public function store()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        $userId = $input['user_id'] ?? null;
        $productId = $input['product_id'] ?? null;
        $salesId = $input['sales_id'] ?? null;

        if (!$userId || !$productId || !$salesId) {
            return Response::json([
                'status' => 'error',
                'message' => 'user_id, product_id, sales_id wajib diisi'
            ], 400);
        }

        $productModel = new ProductModel();
        $product = $productModel->findById($productId);

        if (!$product) {
            return Response::json([
                'status' => 'error',
                'message' => 'Product tidak ditemukan'
            ], 404);
        }

        $price = $product['final_price'] ?? $product['product_price'];

        if (!$price) {
            return Response::json([
                'status' => 'error',
                'message' => 'Harga product tidak valid'
            ], 400);
        }

        $transactionModel = new TransactionModel();

        do {
            $unique = rand(100, 999);
            $total = $price + $unique;

            $exists = $transactionModel->isTotalExists($total);

        } while ($exists);

        $expiredAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $transactionId = $transactionModel->create([
            'user_id' => $userId,
            'product_id' => $productId,
            'sales_id' => $salesId,
            'product_name' => $product['product_name'],
            'product_type' => $product['product_type'],
            'product_price' => $price,
            'total_amount' => $total,
            'expired_at' => $expiredAt
        ]);

        return Response::json([
            'status' => 'success',
            'message' => 'Transaction berhasil dibuat',
            'transaction_id' => $transactionId,
            'payment' => [
                'total_amount' => $total,
                'expired_at' => $expiredAt
            ]
        ]);
    }

    public function confirm()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $data['transaction_id'] ?? null;

        if (!$id) {
            return Response::json([
                'status' => 'error',
                'message' => 'Transaction ID required'
            ], 400);
        }

        $model = new TransactionModel();

        $model->markAsWaitingApproval($id);

        return Response::json([
            'status' => 'success',
            'message' => 'Menunggu approval'
        ]);
    }

    public function index()
    {
        $model = new TransactionModel();
        $data = $model->getAll();

        return Response::json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function approve()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        $id = $input['transaction_id'] ?? null;

        if (!$id) {
            return Response::json([
                'status' => 'error',
                'message' => 'Transaction ID required'
            ], 400);
        }

        $model = new TransactionModel();

        $model->updateStatus($id, 'PAID');

        return Response::json([
            'status' => 'success',
            'message' => 'Transaction approved'
        ]);
    }

    public function sendInvoice($id)
    {
        $model = new TransactionModel();

        try {
            $transaction = $model->findByIdWithUser($id);

            if (!$transaction) {
                return Response::json([
                    'status' => 'error',
                    'message' => 'Transaction not found'
                ], 404);
            }

            if ($transaction['status'] !== 'PAID') {
                return Response::json([
                    'status' => 'error',
                    'message' => 'Transaction not PAID'
                ], 400);
            }

            $filePath = InvoiceService::generate($transaction);

            MailService::sendInvoice($transaction['email'], $filePath);

            $model->updateStatus($id, 'SUCCESS');

            return Response::json([
                'status' => 'success',
                'message' => 'Invoice sent & status updated'
            ]);

        } catch (Exception $e) {

            error_log("Send Invoice Error: " . $e->getMessage());

            return Response::json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}