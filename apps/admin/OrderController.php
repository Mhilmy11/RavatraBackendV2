<?php

declare(strict_types=1);

final class OrderController
{
    private OrderRepository $repository;

    public function __construct()
    {
        $this->repository = new OrderRepository();
    }

    public function store(): void
    {
        AuthMiddleware::handle();

        $request = Request::json();

        $productCode = trim($request['product_code'] ?? '');
        $dealPrice = (float) ($request['deal_price'] ?? 0);
        $notes = trim($request['notes'] ?? '');

        if ($productCode === '') {

            Response::error(
                'Product is required.',
                HTTP_BAD_REQUEST
            );

            return;
        }

        if ($dealPrice <= 0) {

            Response::error(
                'Deal price is required.',
                HTTP_BAD_REQUEST
            );

            return;
        }

        $product = $this->repository->findProductByCode(
            $productCode
        );

        if (!$product) {

            Response::error(
                'Product not found.',
                HTTP_NOT_FOUND
            );

            return;
        }

        $transactionCode =
            $this->repository->generateTransactionCode();

        $checkoutToken = bin2hex(
            random_bytes(32)
        );

        $createdBy = AuthMiddleware::userId();

        $created = $this->repository->create([
            'transaction_code' => $transactionCode,
            'checkout_token' => $checkoutToken,
            'created_by' => $createdBy,
            'product_id' => $product['id'],
            'deal_price' => $dealPrice,
            'notes' => $notes,
        ]);

        if (!$created) {

            Response::error(
                'Failed to create order.',
                HTTP_INTERNAL_SERVER_ERROR
            );

            return;
        }

        Response::success(
            [
                'transaction_code' => $transactionCode,
                'checkout_token' => $checkoutToken,
                'checkout_url' => sprintf(
                    '%s/checkout/%s',
                    APP_URL,
                    $checkoutToken
                ),
            ],
            'Checkout link generated successfully.'
        );
    }
}