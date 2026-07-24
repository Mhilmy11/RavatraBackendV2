<?php

declare(strict_types=1);


Router::post('/account/register', [AuthController::class, 'register']);
Router::post('/account/login', [AuthController::class, 'login']);
Router::get('/account/profile', [AuthController::class, 'profile']);


Router::get('/products/{slug}', [ProductController::class, 'show']);
Router::get('/products', [ProductController::class, 'index']);