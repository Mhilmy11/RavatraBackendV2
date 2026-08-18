<?php

declare(strict_types=1);

final class ProfileController
{
    private ProfileRepository $repository;

    public function __construct()
    {
        $this->repository = new ProfileRepository();
    }

    public function transactions(): void
    {
        AuthMiddleware::handleUser();

        $userId = AuthMiddleware::userId();

        $transactions = $this->repository->getTransactionsByUserId(
            $userId
        );

        Response::success(
            $transactions,
            'Transactions retrieved successfully'
        );
    }

    public function downloadInvoice(
        string $transactionCode
    ): void {
        AuthMiddleware::handleUser();

        $userId = AuthMiddleware::userId();

        $transaction = $this->repository
            ->findInvoiceForUser(
                $transactionCode,
                $userId
            );

        if (!$transaction) {
            Response::error(
                'Transaction not found.',
                HTTP_NOT_FOUND
            );

            return;
        }

        if ($transaction['status'] !== STATUS_PAID) {
            Response::error(
                'Invoice is only available for paid transactions.',
                HTTP_BAD_REQUEST
            );

            return;
        }

        if (empty($transaction['invoice_path'])) {
            Response::error(
                'Invoice is not available.',
                HTTP_NOT_FOUND
            );

            return;
        }

        $filePath = BASE_PATH
            . '/storage/'
            . $transaction['invoice_path'];

        if (!file_exists($filePath)) {
            Response::error(
                'Invoice file not found.',
                HTTP_NOT_FOUND
            );

            return;
        }

        if (!is_readable($filePath)) {
            Response::error(
                'Invoice file cannot be accessed.',
                HTTP_INTERNAL_SERVER_ERROR
            );

            return;
        }

        $fileName = $transaction['invoice_number']
            . '.pdf';

        header('Content-Type: application/pdf');

        header(
            'Content-Disposition: attachment; filename="' .
            $fileName .
            '"'
        );

        header(
            'Content-Length: ' . filesize($filePath)
        );

        header(
            'Cache-Control: private, max-age=0, must-revalidate'
        );

        header('Pragma: public');

        readfile($filePath);

        exit;
    }
}