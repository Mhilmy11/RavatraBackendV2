<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/core/Env.php';

Env::load(BASE_PATH . '/.env');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

require_once BASE_PATH . '/apps/user/ProductController.php';

require_once BASE_PATH . '/auth/AuthController.php';

require_once BASE_PATH . '/config/routes.php';

Router::dispatch();