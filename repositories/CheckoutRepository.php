<?php

declare(strict_types=1);

final class CheckoutRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByToken(
        string $checkoutToken
    ): array|false {

        $sql = "
            SELECT

                t.id,
                t.transaction_code,
                t.checkout_token,

                t.user_id,
                t.created_by,
                t.product_id,

                t.deal_price,
                t.payment_proof,

                t.status,
                t.notes,
                t.reject_reason,

                t.claimed_at,
                t.submitted_at,
                t.approved_at,
                t.rejected_at,
                t.expired_at,

                p.product_code,
                p.slug,
                p.product_name,
                p.product_type,
                p.thumbnail,
                p.schedule,
                p.start_date,
                p.start_end_time,
                p.location,
                p.product_price,

                CONCAT(
                    creator.firstname,
                    ' ',
                    creator.lastname
                ) AS creator_name

            FROM transactions t

            INNER JOIN products p
                ON p.id = t.product_id

            INNER JOIN users creator
                ON creator.id = t.created_by

            WHERE
                t.checkout_token = :checkout_token

            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':checkout_token',
            $checkoutToken,
            PDO::PARAM_STR
        );

        $stmt->execute();

        return $stmt->fetch(
            PDO::FETCH_ASSOC
        );
    }

    public function claimCheckout(
        int $transactionId,
        int $userId
    ): bool {

        $sql = "
            UPDATE transactions
            SET
                user_id = :user_id,
                claimed_at = NOW()
            WHERE
                id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([

            ':user_id' => $userId,

            ':id' => $transactionId,

        ]);
    }

    public function findByCheckoutToken(
        string $checkoutToken
    ): ?array {
        $sql = "
            SELECT
                id,
                transaction_code,
                checkout_token,
                user_id,
                created_by,
                product_id,
                deal_price,
                payment_proof,
                status,
                notes,
                reject_reason,
                claimed_at,
                submitted_at,
                approved_at,
                rejected_at,
                expired_at,
                approved_by,
                rejected_by,
                created_at,
                updated_at
            FROM transactions
            WHERE checkout_token = :checkout_token
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'checkout_token' => $checkoutToken
        ]);

        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

        return $transaction ?: null;
    }

    public function submitPaymentProof(
        int $transactionId,
        string $paymentProof
    ): bool {
        $sql = "
            UPDATE transactions
            SET
                payment_proof = :payment_proof,
                status = :status,
                submitted_at = NOW(),
                updated_at = NOW()
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'payment_proof' => $paymentProof,
            'status' => STATUS_WAITING_APPROVAL,
            'id' => $transactionId
        ]);
    }
}