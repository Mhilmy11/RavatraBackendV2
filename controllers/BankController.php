<?php

require_once __DIR__ . '/../models/BankModel.php';
require_once __DIR__ . '/../core/Response.php';

class BankController
{
    public function index()
    {
        $model = new BankModel();
        $data = $model->getAll();

        return Response::json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}