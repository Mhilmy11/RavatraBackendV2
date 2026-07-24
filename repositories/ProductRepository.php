<?php

declare(strict_types=1);

final class ProductRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get All Active Products
     */
    public function getAll(): array
    {
        $sql = "
            SELECT
                product_code,
                product_name,
                product_type,
                thumbnail,
                schedule,
                start_date,
                start_end_time,
                location,
                product_price
            FROM products
            WHERE status = 'ACTIVE'
            ORDER BY created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}