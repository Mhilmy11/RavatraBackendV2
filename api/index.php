<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/core/Env.php';

Env::load(BASE_PATH . '/.env');

require_once BASE_PATH . '/config/constants.php';

require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Request.php';
require_once BASE_PATH . '/core/Response.php';
require_once BASE_PATH . '/core/Router.php';

require_once BASE_PATH . '/repositories/ProductRepository.php';
require_once BASE_PATH . '/apps/admin/ProductController.php';

require_once BASE_PATH . '/config/routes.php';

Router::dispatch();