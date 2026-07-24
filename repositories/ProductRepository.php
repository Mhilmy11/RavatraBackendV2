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
     * Get Product List
     *
     * Available Filters:
     * - type
     * - featured
     * - search
     * - limit
     */
    public function getAll(array $filters = []): array
    {
        $sql = "
            SELECT
                product_code,
                slug,
                product_name,
                product_type,
                thumbnail,
                schedule,
                start_date,
                start_end_time,
                location,
                product_price,
                is_featured
            FROM products
            WHERE status = 'ACTIVE'
        ";

        $params = [];

        /**
         * Filter Product Type
         */
        if (isset($filters['type'])) {
            $sql .= " AND product_type = :type";
            $params['type'] = strtoupper($filters['type']);
        }

        /**
         * Featured Product
         */
        if (isset($filters['featured'])) {
            $sql .= " AND is_featured = :featured";
            $params['featured'] = (int) $filters['featured'];
        }

        /**
         * Search Product
         */
        if (!empty($filters['search'])) {
            $sql .= " AND product_name LIKE :search";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        /**
         * Sort Product
         */
        $sql .= " ORDER BY start_date ASC, created_at DESC";

        /**
         * Limit Product
         */
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(
                ':' . $key,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }

        if (!empty($filters['limit'])) {
            $stmt->bindValue(
                ':limit',
                (int) $filters['limit'],
                PDO::PARAM_INT
            );
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find Product By Slug
     */
    public function findBySlug(string $slug): ?array
    {
        $sql = "
        SELECT
            product_code,
            slug,
            product_name,
            product_type,
            thumbnail,
            schedule,
            start_date,
            start_end_time,
            location,
            product_price,
            description,
            outline_materi,
            pembicara,
            facility,
            package_link,
            is_featured,
            status,
            created_at,
            updated_at
        FROM products
        WHERE slug = :slug
        LIMIT 1
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);

        $stmt->execute();

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        return $product ?: null;
    }
}