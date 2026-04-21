<?php

require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../core/Response.php';

class ProductController
{
    public function index()
    {
        $type = $_GET['type'] ?? null;

        $model = new ProductModel();

        if ($type) {
            $data = $model->getByType($type);
        } else {
            $data = $model->getAll();
        }

        return Response::json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function show($id)
    {
        $model = new ProductModel();
        $product = $model->findById($id);

        if (!$product) {
            return Response::json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        }

        return Response::json([
            'status' => 'success',
            'data' => $product
        ]);
    }

    public function store()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (
            empty($data['product_name']) ||
            empty($data['product_type']) ||
            empty($data['product_price'])
        ) {
            return Response::json([
                'status' => 'error',
                'message' => 'Data wajib tidak lengkap'
            ], 400);
        }

        $model = new ProductModel();
        $model->create($data);

        return Response::json([
            'status' => 'success',
            'message' => 'Product created'
        ]);
    }

    public function update($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $model = new ProductModel();
        $model->update($id, $data);

        return Response::json([
            'status' => 'success',
            'message' => 'Product updated'
        ]);
    }

    public function destroy($id)
    {
        $model = new ProductModel();

        $model->delete($id);

        return Response::json([
            'status' => 'success',
            'message' => 'Product deleted'
        ]);
    }
}