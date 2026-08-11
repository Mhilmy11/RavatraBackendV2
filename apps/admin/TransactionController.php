<?php

declare(strict_types=1);

require_once BASE_PATH . '/services/InvoiceService.php';

final class TransactionController
{
    private TransactionRepository $repository;
    private InvoiceService $invoiceService;


    public function __construct()
    {
        $this->repository = new TransactionRepository();
        $this->invoiceService = new InvoiceService();
    }

    public function index(): never
    {
        $filters = [];

        if (!empty($_GET['search'])) {
            $filters['search'] = trim($_GET['search']);
        }

        if (!empty($_GET['status'])) {
            $filters['status'] = trim($_GET['status']);
        }

        $page = !empty($_GET['page'])
            ? max(1, (int) $_GET['page'])
            : 1;

        $limit = !empty($_GET['limit'])
            ? min(50, max(1, (int) $_GET['limit']))
            : 10;


        $filters['page'] = $page;
        $filters['limit'] = $limit;

        $transactions = $this->repository->getAll($filters);

        $total = $this->repository->countAll($filters);

        $totalPages = (int) ceil($total / $limit);


        Response::success(
            [
                'transactions' => $transactions,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => $totalPages
                ]
            ],
            'Transactions retrieved successfully'
        );
    }

    public function show(string $transactionCode): void
    {
        $transaction = $this->repository
            ->findByTransactionCode($transactionCode);


        if (!$transaction) {
            Response::error(
                'Transaction not found',
                HTTP_NOT_FOUND
            );

            return;
        }


        Response::success(
            $transaction,
            'Transaction retrieved successfully'
        );
    }

    public function approve(string $transactionCode): void
    {
        $transaction = $this->repository
            ->findByTransactionCode($transactionCode);

        if (!$transaction) {
            Response::error(
                'Transaction not found',
                HTTP_NOT_FOUND
            );

            return;
        }

        if ($transaction['status'] !== 'WAITING_APPROVAL') {
            Response::error(
                'Only transactions with WAITING_APPROVAL status can be approved',
                HTTP_BAD_REQUEST
            );

            return;
        }

        $adminId = (int) ($_SESSION['user_id'] ?? 0);

        if ($adminId <= 0) {
            Response::error(
                'Unauthorized',
                HTTP_UNAUTHORIZED
            );

            return;
        }

        $approved = $this->repository->approve(
            $transactionCode,
            $adminId
        );


        if (!$approved) {
            Response::error(
                'Failed to approve transaction',
                HTTP_BAD_REQUEST
            );

            return;
        }


        Response::success(
            null,
            'Transaction approved successfully'
        );
    }

    public function reject(string $transactionCode): void
    {
        $transaction = $this->repository
            ->findByTransactionCode($transactionCode);

        if (!$transaction) {
            Response::error(
                'Transaction not found',
                HTTP_NOT_FOUND
            );

            return;
        }

        if ($transaction['status'] !== 'WAITING_APPROVAL') {
            Response::error(
                'Only transactions with WAITING_APPROVAL status can be rejected',
                HTTP_BAD_REQUEST
            );

            return;
        }

        $input = json_decode(
            file_get_contents('php://input'),
            true
        );

        $rejectReason = trim(
            $input['reject_reason'] ?? ''
        );

        if ($rejectReason === '') {
            Response::error(
                'Reject reason is required',
                HTTP_BAD_REQUEST
            );

            return;
        }

        $adminId = (int) ($_SESSION['user_id'] ?? 0);

        if ($adminId <= 0) {
            Response::error(
                'Unauthorized',
                HTTP_UNAUTHORIZED
            );

            return;
        }

        $rejected = $this->repository->reject(
            $transactionCode,
            $rejectReason,
            $adminId
        );


        if (!$rejected) {
            Response::error(
                'Failed to reject transaction',
                HTTP_BAD_REQUEST
            );

            return;
        }


        Response::success(
            null,
            'Transaction rejected successfully'
        );
    }

    public function generateInvoice(string $transactionCode): void
    {
        /**
         * Find Transaction
         */
        $transaction = $this->repository->findForInvoice(
            $transactionCode
        );

        if (!$transaction) {
            Response::error(
                'Transaction not found',
                HTTP_NOT_FOUND
            );

            return;
        }

        /**
         * Invoice can only be generated
         * for PAID transaction
         */
        if ($transaction['status'] !== 'PAID') {
            Response::error(
                'Invoice can only be generated for paid transactions',
                HTTP_BAD_REQUEST
            );

            return;
        }

        /**
         * Invoice already exists
         */
        if (
            !empty($transaction['invoice_number']) &&
            !empty($transaction['invoice_path'])
        ) {
            Response::success(
                [
                    'invoice_number' => $transaction['invoice_number'],
                    'invoice_path' => $transaction['invoice_path'],
                    'invoice_generated_at' =>
                        $transaction['invoice_generated_at'],
                ],
                'Invoice already exists'
            );

            return;
        }

        try {
            /**
             * Generate Invoice
             */
            $invoice = $this->invoiceService->generate(
                $transaction
            );

            /**
             * Save Invoice Information
             */
            $saved = $this->repository->saveInvoice(
                (int) $transaction['id'],
                $invoice['invoice_number'],
                $invoice['invoice_path'],
                $invoice['invoice_generated_at']
            );

            if (!$saved) {
                Response::error(
                    'Failed to save invoice information',
                    HTTP_INTERNAL_SERVER_ERROR
                );

                return;
            }

            /**
             * Success
             */
            Response::success(
                $invoice,
                'Invoice generated successfully'
            );
        } catch (Throwable $e) {
            Response::error(
                'Failed to generate invoice',
                HTTP_INTERNAL_SERVER_ERROR
            );

            return;
        }
    }
}