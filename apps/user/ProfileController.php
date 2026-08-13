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
}