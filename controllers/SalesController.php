<?php

require_once __DIR__ . '/../models/SalesModel.php';
require_once __DIR__ . '/../core/Response.php';

class SalesController
{
    public function index()
    {
        $model = new SalesModel();
        $sales = $model->getAll();

        return Response::json([
            'status' => 'success',
            'data' => $sales
        ]);
    }
}