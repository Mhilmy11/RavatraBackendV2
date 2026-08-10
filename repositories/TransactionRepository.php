<?php

declare(strict_types=1);

final class TransactionRepository
{
    private PDO $db;


    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(array $filters = []): array
    {
        $sql = "
        SELECT
            t.transaction_code,
            t.deal_price,
            t.status,

            CONCAT(
                customer.firstname,
                ' ',
                customer.lastname
            ) AS customer_name,

            CONCAT(
                sales.firstname,
                ' ',
                sales.lastname
            ) AS sales_name,

            p.product_name

        FROM transactions t

        LEFT JOIN users customer
            ON customer.id = t.user_id

        LEFT JOIN users sales
            ON sales.id = t.created_by

        INNER JOIN products p
            ON p.id = t.product_id

        WHERE 1 = 1
    ";

        $params = [];


        /**
         * Search Transaction
         */
        if (!empty($filters['search'])) {
            $sql .= "
            AND (
                t.transaction_code LIKE :search_transaction
                OR CONCAT(
                    customer.firstname,
                    ' ',
                    customer.lastname
                ) LIKE :search_customer
                OR CONCAT(
                    sales.firstname,
                    ' ',
                    sales.lastname
                ) LIKE :search_sales
                OR p.product_name LIKE :search_product
            )
        ";

            $search = '%' . $filters['search'] . '%';

            $params['search_transaction'] = $search;
            $params['search_customer'] = $search;
            $params['search_sales'] = $search;
            $params['search_product'] = $search;
        }


        /**
         * Filter Transaction Status
         */
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";

            $params['status'] = strtoupper(
                $filters['status']
            );
        }


        /**
         * Sort Transaction
         */
        $sql .= " ORDER BY t.created_at DESC";


        /**
         * Pagination
         */
        if (!empty($filters['limit'])) {

            $page = max(
                1,
                (int) ($filters['page'] ?? 1)
            );

            $limit = (int) $filters['limit'];

            $offset = ($page - 1) * $limit;

            $sql .= " LIMIT :limit OFFSET :offset";
        }


        $stmt = $this->db->prepare($sql);


        foreach ($params as $key => $value) {
            $stmt->bindValue(
                ':' . $key,
                $value,
                PDO::PARAM_STR
            );
        }


        if (!empty($filters['limit'])) {

            $stmt->bindValue(
                ':limit',
                $limit,
                PDO::PARAM_INT
            );

            $stmt->bindValue(
                ':offset',
                $offset,
                PDO::PARAM_INT
            );
        }


        $stmt->execute();


        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll(array $filters = []): int
    {
        $sql = "
        SELECT COUNT(*)

        FROM transactions t

        LEFT JOIN users customer
            ON customer.id = t.user_id

        LEFT JOIN users sales
            ON sales.id = t.created_by

        INNER JOIN products p
            ON p.id = t.product_id

        WHERE 1 = 1
    ";

        $params = [];


        /**
         * Search Transaction
         */
        if (!empty($filters['search'])) {
            $sql .= "
            AND (
                t.transaction_code LIKE :search_transaction
                OR CONCAT(
                    customer.firstname,
                    ' ',
                    customer.lastname
                ) LIKE :search_customer
                OR CONCAT(
                    sales.firstname,
                    ' ',
                    sales.lastname
                ) LIKE :search_sales
                OR p.product_name LIKE :search_product
            )
        ";

            $search = '%' . $filters['search'] . '%';

            $params['search_transaction'] = $search;
            $params['search_customer'] = $search;
            $params['search_sales'] = $search;
            $params['search_product'] = $search;
        }


        /**
         * Filter Transaction Status
         */
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";

            $params['status'] = strtoupper(
                $filters['status']
            );
        }


        $stmt = $this->db->prepare($sql);


        foreach ($params as $key => $value) {
            $stmt->bindValue(
                ':' . $key,
                $value,
                PDO::PARAM_STR
            );
        }


        $stmt->execute();


        return (int) $stmt->fetchColumn();
    }

    public function findByTransactionCode(
        string $transactionCode
    ): ?array {
        $sql = "
        SELECT
            t.transaction_code,
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
            t.created_at,
            t.updated_at,

            CONCAT(
                customer.firstname,
                ' ',
                customer.lastname
            ) AS customer_name,

            CONCAT(
                sales.firstname,
                ' ',
                sales.lastname
            ) AS sales_name,

            p.product_name

        FROM transactions t

        LEFT JOIN users customer
            ON customer.id = t.user_id

        LEFT JOIN users sales
            ON sales.id = t.created_by

        INNER JOIN products p
            ON p.id = t.product_id

        WHERE t.transaction_code = :transaction_code

        LIMIT 1
    ";


        $stmt = $this->db->prepare($sql);


        $stmt->bindValue(
            ':transaction_code',
            $transactionCode,
            PDO::PARAM_STR
        );


        $stmt->execute();


        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);


        return $transaction ?: null;
    }

    public function approve(
        string $transactionCode,
        int $adminId
    ): bool {
        $sql = "
            UPDATE transactions
            SET
                status = 'PAID',
                approved_by = :approved_by,
                approved_at = NOW(),
                updated_at = NOW()
            WHERE transaction_code = :transaction_code
                AND status = 'WAITING_APPROVAL'
        ";


        $stmt = $this->db->prepare($sql);


        $stmt->bindValue(
            ':approved_by',
            $adminId,
            PDO::PARAM_INT
        );


        $stmt->bindValue(
            ':transaction_code',
            $transactionCode,
            PDO::PARAM_STR
        );


        $stmt->execute();


        return $stmt->rowCount() > 0;
    }

    public function reject(
        string $transactionCode,
        string $rejectReason,
        int $adminId
    ): bool {
        $sql = "
        UPDATE transactions
        SET
            status = 'REJECTED',
            reject_reason = :reject_reason,
            rejected_by = :rejected_by,
            rejected_at = NOW(),
            updated_at = NOW()
        WHERE transaction_code = :transaction_code
            AND status = 'WAITING_APPROVAL'
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':reject_reason',
            $rejectReason,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':rejected_by',
            $adminId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':transaction_code',
            $transactionCode,
            PDO::PARAM_STR
        );

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}