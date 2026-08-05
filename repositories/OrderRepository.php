<?php

declare(strict_types=1);

final class OrderRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findProductByCode(string $productCode): array|false
    {
        $sql = "
            SELECT
                id,
                product_code,
                product_name,
                product_price
            FROM products
            WHERE product_code = :product_code
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':product_code',
            $productCode,
            PDO::PARAM_STR
        );

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function generateTransactionCode(): string
    {
        $prefix = 'TRX';
        $date = date('Ymd');

        $sql = "
            SELECT COUNT(*) AS total
            FROM transactions
            WHERE DATE(created_at) = CURDATE()
        ";

        $stmt = $this->db->query($sql);

        $total = (int) $stmt->fetch()['total'] + 1;

        return sprintf(
            '%s%s%05d',
            $prefix,
            $date,
            $total
        );
    }

    public function create(array $data): bool
    {
        $sql = "
            INSERT INTO transactions
            (
                transaction_code,
                checkout_token,
                created_by,
                product_id,
                deal_price,
                status,
                notes,
                expired_at
            )
            VALUES
            (
                :transaction_code,
                :checkout_token,
                :created_by,
                :product_id,
                :deal_price,
                'PENDING',
                :notes,
                DATE_ADD(NOW(), INTERVAL 7 DAY)
            )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':transaction_code' => $data['transaction_code'],
            ':checkout_token' => $data['checkout_token'],
            ':created_by' => $data['created_by'],
            ':product_id' => $data['product_id'],
            ':deal_price' => $data['deal_price'],
            ':notes' => $data['notes'],
        ]);
    }
}