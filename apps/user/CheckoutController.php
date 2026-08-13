<?php

declare(strict_types=1);

final class CheckoutController
{
    private CheckoutRepository $repository;

    public function __construct()
    {
        $this->repository = new CheckoutRepository();
    }

    public function show(string $checkoutToken): void
    {
        AuthMiddleware::handleUser();

        $checkout = $this->repository->findByToken(
            $checkoutToken
        );

        if (!$checkout) {
            Response::error(
                'Checkout not found.',
                HTTP_NOT_FOUND
            );

            return;
        }

        if (
            $checkout['expired_at'] !== null &&
            strtotime($checkout['expired_at']) < time()
        ) {
            Response::error(
                'Checkout has expired.',
                HTTP_BAD_REQUEST
            );

            return;
        }

        if ($checkout['status'] !== STATUS_PENDING) {
            Response::error(
                'Checkout is no longer available.',
                HTTP_BAD_REQUEST
            );

            return;
        }

        $currentUserId = AuthMiddleware::userId();

        if ($checkout['user_id'] === null) {
            $claimed = $this->repository->claimCheckout(
                (int) $checkout['id'],
                $currentUserId
            );

            if (!$claimed) {
                Response::error(
                    'Checkout could not be claimed.',
                    HTTP_BAD_REQUEST
                );

                return;
            }

            $checkout['user_id'] = $currentUserId;
        }

        if ((int) $checkout['user_id'] !== $currentUserId) {
            Response::error(
                'This checkout belongs to another account.',
                HTTP_FORBIDDEN
            );

            return;
        }

        Response::success(
            [
                'transaction_code' => $checkout['transaction_code'],
                'checkout_token' => $checkout['checkout_token'],
                'deal_price' => $checkout['deal_price'],
                'notes' => $checkout['notes'],
                'product' => [
                    'product_code' => $checkout['product_code'],
                    'product_name' => $checkout['product_name'],
                    'slug' => $checkout['slug'],
                    'product_type' => $checkout['product_type'],
                    'thumbnail' => $checkout['thumbnail'],
                    'schedule' => $checkout['schedule'],
                    'start_date' => $checkout['start_date'],
                    'start_end_time' => $checkout['start_end_time'],
                    'location' => $checkout['location'],
                    'product_price' => $checkout['product_price'],
                ],
                'created_by' => [
                    'name' => $checkout['creator_name']
                ]
            ],
            'Checkout found.'
        );
    }

    public function submitPaymentProof(string $checkoutToken): void
    {
        AuthMiddleware::handleUser();

        $userId = AuthMiddleware::userId();

        $transaction = $this->repository->findByCheckoutToken(
            $checkoutToken
        );

        if (!$transaction) {
            Response::error(
                'Transaction not found.',
                HTTP_NOT_FOUND
            );

            return;
        }

        if ((int) $transaction['user_id'] !== $userId) {
            Response::error(
                'You are not allowed to access this transaction.',
                HTTP_FORBIDDEN
            );

            return;
        }

        if ($transaction['status'] !== STATUS_PENDING) {
            Response::error(
                'Payment proof cannot be submitted for this transaction.',
                HTTP_BAD_REQUEST
            );

            return;
        }

        if (
            !empty($transaction['expired_at']) &&
            strtotime($transaction['expired_at']) < time()
        ) {
            Response::error(
                'This checkout has expired.',
                HTTP_BAD_REQUEST
            );

            return;
        }

        $file = Request::file('payment_proof');

        if (!$file) {
            Response::error(
                'Payment proof is required.',
                HTTP_BAD_REQUEST
            );

            return;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            Response::error(
                'Failed to upload payment proof.',
                HTTP_BAD_REQUEST
            );

            return;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            Response::error(
                'Payment proof must not exceed 5 MB.',
                HTTP_BAD_REQUEST
            );

            return;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);

        $mimeType = $finfo->file(
            $file['tmp_name']
        );

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf'
        ];

        if (!isset($allowedTypes[$mimeType])) {
            Response::error(
                'Invalid payment proof format. Only JPG, PNG, and PDF are allowed.',
                HTTP_BAD_REQUEST
            );

            return;
        }

        $extension = $allowedTypes[$mimeType];

        $fileName =
            $transaction['transaction_code']
            . '_'
            . bin2hex(random_bytes(8))
            . '.'
            . $extension;

        if (!is_dir(PAYMENT_UPLOAD_PATH)) {
            mkdir(
                PAYMENT_UPLOAD_PATH,
                0755,
                true
            );
        }

        $destination =
            PAYMENT_UPLOAD_PATH . $fileName;

        if (
            !move_uploaded_file(
                $file['tmp_name'],
                $destination
            )
        ) {
            Response::error(
                'Failed to save payment proof.',
                HTTP_INTERNAL_SERVER_ERROR
            );

            return;
        }

        $paymentProof = 'payment/' . $fileName;

        $updated = $this->repository->submitPaymentProof(
            (int) $transaction['id'],
            $paymentProof
        );

        if (!$updated) {
            if (file_exists($destination)) {
                unlink($destination);
            }

            Response::error(
                'Failed to submit payment proof.',
                HTTP_INTERNAL_SERVER_ERROR
            );

            return;
        }

        Response::success(
            [
                'transaction_code' => $transaction['transaction_code'],
                'payment_proof' => $paymentProof,
                'status' => STATUS_WAITING_APPROVAL
            ],
            'Payment proof submitted successfully.'
        );
    }
}