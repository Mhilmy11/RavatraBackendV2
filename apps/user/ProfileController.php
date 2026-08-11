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
        $userId = (int) ($_SESSION['user_id'] ?? 0);

        if ($userId <= 0) {
            Response::error(
                'Unauthorized.',
                HTTP_UNAUTHORIZED
            );

            return;
        }

        $transactions = $this->repository->getTransactionsByUserId(
            $userId
        );

        Response::success(
            $transactions,
            'Transactions retrieved successfully'
        );
    }
}