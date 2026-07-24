<?php

declare(strict_types=1);

// =========================
// USER
// =========================

// Router::post('/auth/login', [AuthController::class, 'login']);


// =========================
// ADMIN
// =========================

Router::get('/products', [ProductController::class, 'index']);