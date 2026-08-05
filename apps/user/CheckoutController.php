<?php

declare(strict_types=1);

final class CheckoutController
{
    private CheckoutRepository $repository;


    public function __construct()
    {
        $this->repository = new CheckoutRepository();
    }


    /**
     * Show Checkout
     */
    public function show(
        string $checkoutToken
    ): void {

        /**
         * User Login Required
         */
        AuthMiddleware::handle();

        /**
         * Find Checkout
         */
        $checkout = $this->repository
            ->findByToken($checkoutToken);


        if (!$checkout) {

            Response::error(
                'Checkout not found.',
                HTTP_NOT_FOUND
            );

            return;
        }
        /**
         * Check Expired
         */
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
        /**
         * Check Status
         */
        if (
            $checkout['status'] !== 'PENDING'
        ) {

            Response::error(
                'Checkout is no longer available.',
                HTTP_BAD_REQUEST
            );

            return;
        }
        /**
         * Current Customer
         */
        $currentUserId =
            AuthMiddleware::userId();

        /**
         * Claim Checkout
         */
        if (
            $checkout['user_id'] === null
        ) {

            $this->repository->claimCheckout(
                (int) $checkout['id'],
                $currentUserId
            );


            $checkout['user_id'] =
                $currentUserId;
        }
        /**
         * Prevent another user claim
         */
        if (
            (int) $checkout['user_id']
            !== $currentUserId
        ) {

            Response::error(
                'This checkout belongs to another account.',
                HTTP_FORBIDDEN
            );

            return;
        }
        /**
         * Response
         */
        Response::success(

            [
                'transaction_code'
                => $checkout['transaction_code'],
                'checkout_token'
                => $checkout['checkout_token'],
                'deal_price'
                => $checkout['deal_price'],
                'notes'
                => $checkout['notes'],
                'product' => [
                    'product_code'
                    => $checkout['product_code'],
                    'product_name'
                    => $checkout['product_name'],
                    'slug'
                    => $checkout['slug'],
                    'product_type'
                    => $checkout['product_type'],
                    'thumbnail'
                    => $checkout['thumbnail'],
                    'schedule'
                    => $checkout['schedule'],
                    'start_date'
                    => $checkout['start_date'],
                    'start_end_time'
                    => $checkout['start_end_time'],
                    'location'
                    => $checkout['location'],
                    'product_price'
                    => $checkout['product_price'],
                ],
                /**
                 * Created By
                 */
                'created_by' => [
                    'name'
                    => $checkout['creator_name']
                ]

            ],

            'Checkout found.'

        );

    }
}