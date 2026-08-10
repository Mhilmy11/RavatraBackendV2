<?php

declare(strict_types=1);


Router::post('/account/register', [AuthController::class, 'register']);
Router::post('/account/login', [AuthController::class, 'loginUser']);
Router::post('/account/logout', [AuthController::class, 'logout']);
Router::get('/account/profile', [AuthController::class, 'profile']);


Router::get('/products/{slug}', [ProductController::class, 'show']);
Router::get('/products', [ProductController::class, 'index']);


Router::post('/admin/login', [AuthController::class, 'loginAdmin']);
Router::post('/admin/logout', [AuthController::class, 'logout']);
Router::get('/admin/profile', [AuthController::class, 'profile']);
Router::post('/admin/orders', [OrderController::class, 'store']);
Router::get('/admin/transactions', [TransactionController::class, 'index']);
Router::get('/admin/transactions/{transaction_code}', [TransactionController::class, 'show']);
Router::post('/admin/transactions/{transaction_code}/approve', [TransactionController::class, 'approve']);
Router::post('/admin/transactions/{transaction_code}/reject', [TransactionController::class, 'reject']);


Router::get('/checkout/{checkout_token}', [CheckoutController::class, 'show']);
Router::post('/checkout/{checkoutToken}/payment', [CheckoutController::class, 'submitPaymentProof']);