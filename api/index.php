<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Root Path
|--------------------------------------------------------------------------
*/

define('BASE_PATH', dirname(__DIR__));

/*
|--------------------------------------------------------------------------
| Load Core
|--------------------------------------------------------------------------
*/

require_once BASE_PATH . '/core/Env.php';

Env::load(BASE_PATH . '/.env');

require_once BASE_PATH . '/config/constants.php';

require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Request.php';
require_once BASE_PATH . '/core/Response.php';
require_once BASE_PATH . '/core/Router.php';

/*
|--------------------------------------------------------------------------
| Load Routes
|--------------------------------------------------------------------------
*/

require_once BASE_PATH . '/config/routes.php';

/*
|--------------------------------------------------------------------------
| Run Router
|--------------------------------------------------------------------------
*/

Router::dispatch();