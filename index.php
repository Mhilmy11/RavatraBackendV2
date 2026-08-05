<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/core/Env.php';

Env::load(BASE_PATH . '/.env');

// =========================
// SESSION
// =========================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =========================
// CORS
// =========================

$allowedOrigins = [
    'http://localhost:5173',
    'https://ravatraacademy.id',
    'https://www.ravatraacademy.id',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: {$origin}");
}

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once BASE_PATH . '/config/constants.php';

require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Request.php';
require_once BASE_PATH . '/core/Response.php';
require_once BASE_PATH . '/core/Router.php';

require_once BASE_PATH . '/middlewares/AuthMiddleware.php';

require_once BASE_PATH . '/utils/CodeGenerator.php';

require_once BASE_PATH . '/repositories/ProductRepository.php';
require_once BASE_PATH . '/repositories/AuthRepository.php';
require_once BASE_PATH . '/repositories/CheckoutRepository.php';
require_once BASE_PATH . '/repositories/OrderRepository.php';

require_once BASE_PATH . '/apps/user/ProductController.php';
require_once BASE_PATH . '/apps/user/CheckoutController.php';

require_once BASE_PATH . '/apps/admin/OrderController.php';

require_once BASE_PATH . '/auth/AuthController.php';


require_once BASE_PATH . '/config/routes.php';

Router::dispatch();