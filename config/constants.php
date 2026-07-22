<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| User Roles
|--------------------------------------------------------------------------
*/

define('ROLE_ADMIN', 'ADMIN');
define('ROLE_USER', 'USER');

/*
|--------------------------------------------------------------------------
| User Status
|--------------------------------------------------------------------------
*/

define('STATUS_ACTIVE', 'ACTIVE');
define('STATUS_INACTIVE', 'INACTIVE');

/*
|--------------------------------------------------------------------------
| Transaction Status
|--------------------------------------------------------------------------
*/

define('STATUS_PENDING', 'PENDING');
define('STATUS_WAITING_APPROVAL', 'WAITING_APPROVAL');
define('STATUS_PAID', 'PAID');
define('STATUS_REJECTED', 'REJECTED');
define('STATUS_EXPIRED', 'EXPIRED');

/*
|--------------------------------------------------------------------------
| Upload Directory
|--------------------------------------------------------------------------
*/

define('PAYMENT_UPLOAD_PATH', __DIR__ . '/../storage/payment/');
define('INVOICE_UPLOAD_PATH', __DIR__ . '/../storage/invoice/');

/*
|--------------------------------------------------------------------------
| HTTP Status Code
|--------------------------------------------------------------------------
*/

define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_INTERNAL_SERVER_ERROR', 500);