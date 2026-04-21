<?php

require_once __DIR__ . '/../core/Model.php';

class ProductModel extends Model
{
    protected $table = 'products';

    public function getAll()
    {
        $stmt = $this->db->query("
            SELECT 
                id,
                product_name,
                product_type,
                schedule,
                start_date,
                start_end_time,
                location,
                product_price,
                discount,
                package_link,
                description,
                outline_materi,
                pembicara,
                facility
            FROM {$this->table}
            ORDER BY id DESC
        ");

        return $stmt->fetchAll();
    }

    public function getByType($type)
    {
        $stmt = $this->db->prepare("
        SELECT 
            p.id,
            p.product_name,
            p.product_type,
            p.schedule,
            p.start_date,
            p.start_end_time,
            p.location,
            p.product_price,
            p.discount,
            p.package_link,
            p.description,
            p.outline_materi,
            p.pembicara,
            p.facility,

            COUNT(t.id) AS pendaftar

        FROM products p

        LEFT JOIN transactions t 
            ON t.product_id = p.id 
            AND t.status = 'success'

        WHERE p.product_type = ?

        GROUP BY p.id

        ORDER BY p.id DESC
    ");

        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
        SELECT 
            id,
            product_name,
            product_type,
            schedule,
            start_date,
            start_end_time,
            location,
            product_price,
            discount,
            description,
            outline_materi,
            pembicara,
            facility,
            package_link
        FROM {$this->table}
        WHERE id = ?
        LIMIT 1
    ");

        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            return null;
        }

        $product['facility'] = $product['facility']
            ? json_decode($product['facility'], true)
            : [];

        $product['pendaftar'] = $this->getPendaftarCount($id);

        return $product;
    }

    public function getPendaftarCount($productId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM transactions
            WHERE product_id = ? AND status = 'success'
        ");

        $stmt->execute([$productId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $result['total'];
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
        INSERT INTO products (
            product_name,
            product_type,
            schedule,
            start_date,
            start_end_time,
            location,
            product_price,
            discount,
            package_link,
            description,
            outline_materi,
            pembicara,
            facility
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

        return $stmt->execute([
            $data['product_name'],
            $data['product_type'],
            $data['schedule'] ?? null,
            $data['start_date'] ?? null,
            $data['start_end_time'] ?? null,
            $data['location'] ?? null,
            $data['product_price'],
            $data['discount'] ?? 0,
            $data['package_link'] ?? null,
            $data['description'] ?? null,
            $data['outline_materi'] ?? null,
            $data['pembicara'] ?? null,
            isset($data['facility']) ? json_encode($data['facility']) : null
        ]);
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("
        UPDATE products SET
            product_name = ?,
            product_type = ?,
            schedule = ?,
            start_date = ?,
            start_end_time = ?,
            location = ?,
            product_price = ?,
            discount = ?,
            package_link = ?,
            description = ?,
            outline_materi = ?,
            pembicara = ?,
            facility = ?
        WHERE id = ?
    ");

        return $stmt->execute([
            $data['product_name'],
            $data['product_type'],
            $data['schedule'] ?? null,
            $data['start_date'] ?? null,
            $data['start_end_time'] ?? null,
            $data['location'] ?? null,
            $data['product_price'],
            $data['discount'] ?? 0,
            $data['package_link'] ?? null,
            $data['description'] ?? null,
            $data['outline_materi'] ?? null,
            $data['pembicara'] ?? null,
            isset($data['facility']) ? json_encode($data['facility']) : null,
            $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }
}