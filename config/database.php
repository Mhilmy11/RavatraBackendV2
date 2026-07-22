<?php

declare(strict_types=1);

/**
 * ------------------------------------------------------------
 * Database Configuration
 * ------------------------------------------------------------
 * Database connection configuration loaded from .env
 */

return [

    'driver' => 'mysql',

    'host' => Env::get('DB_HOST'),

    'port' => Env::get('DB_PORT', '3306'),

    'database' => Env::get('DB_NAME'),

    'username' => Env::get('DB_USER'),

    'password' => Env::get('DB_PASS'),

    'charset' => 'utf8mb4',

    'collation' => 'utf8mb4_unicode_ci',

];