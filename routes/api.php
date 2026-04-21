<?php

$router = new Router();

$router->get('/api/products', 'ProductController@index');
$router->get('/api/products/{id}', 'ProductController@show');
$router->post('/api/products', 'ProductController@store');
$router->put('/api/products/{id}', 'ProductController@update');
$router->delete('/api/products/{id}', 'ProductController@destroy');

$router->get('/api/sales', 'SalesController@index');

$router->post('/api/users', 'UserController@store');
$router->get('/api/users/getuser', 'UserController@index');

$router->post('/api/transactions', 'TransactionController@store');
$router->post('/api/transactions/confirm', 'TransactionController@confirm');
$router->get('/api/transactions/getorder', 'TransactionController@index');
$router->post('/api/transactions/approve', 'TransactionController@approve');
$router->post('/api/transactions/send-invoice/{id}', 'TransactionController@sendInvoice');

$router->post('/api/checkout', 'CheckoutController@store');

$router->get('/api/banks', 'BankController@index');