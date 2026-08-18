<?php

declare(strict_types=1);

final class ProfileRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getTransactionsByUserId(int $userId): array
    {
        $sql = "
            SELECT
                t.id,
                t.transaction_code,
                t.deal_price,
                t.payment_proof,
                t.status,
                t.invoice_number,
                t.invoice_path,
                t.invoice_generated_at,
                t.notes,
                t.reject_reason,
                t.claimed_at,
                t.submitted_at,
                t.approved_at,
                t.rejected_at,
                t.expired_at,
                t.created_at,
                t.updated_at,

                p.product_code,
                p.product_name,
                p.slug,
                p.product_type,
                p.thumbnail,
                p.schedule,
                p.start_date,
                p.start_end_time,
                p.location

            FROM transactions t

            INNER JOIN products p
                ON p.id = t.product_id

            WHERE t.user_id = :user_id

            ORDER BY t.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':user_id',
            $userId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findInvoiceForUser(
        string $transactionCode,
        int $userId
    ): ?array {
        $sql = "
        SELECT
            t.id,
            t.transaction_code,
            t.user_id,
            t.status,
            t.invoice_number,
            t.invoice_path,
            t.invoice_generated_at

        FROM transactions t

        WHERE t.transaction_code = :transaction_code
            AND t.user_id = :user_id

        LIMIT 1
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':transaction_code',
            $transactionCode,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':user_id',
            $userId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

        return $transaction ?: null;
    }
}