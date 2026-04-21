<?php

require_once __DIR__ . '/../core/Model.php';

class TransactionModel extends Model
{
    protected $table = 'transactions';

    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table}
            (
                user_id,
                product_id,
                sales_id,
                product_name,
                product_type,
                product_price,
                total_amount,
                status,
                expired_at,
                created_at
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDING', ?, NOW())
        ");

        $stmt->execute([
            $data['user_id'],
            $data['product_id'],
            $data['sales_id'],
            $data['product_name'],
            $data['product_type'],
            $data['product_price'],
            $data['total_amount'],
            $data['expired_at']
        ]);

        return $this->db->lastInsertId();
    }

    public function isTotalExists($total)
    {
        $stmt = $this->db->prepare("
            SELECT id FROM {$this->table}
            WHERE total_amount = ?
            AND status IN ('PENDING','WAITING_APPROVAL')
            LIMIT 1
        ");

        $stmt->execute([$total]);
        return $stmt->fetch();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
        SELECT id, total_amount, status, expired_at
        FROM {$this->table}
        WHERE id = ?
        LIMIT 1
    ");

        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function markAsWaitingApproval($id)
    {
        $stmt = $this->db->prepare("
        UPDATE {$this->table}
        SET 
            status = 'waiting_approval',
            payment_sent_at = NOW(),
            updated_at = NOW()
        WHERE id = ?
    ");

        return $stmt->execute([$id]);
    }

    public function getAll()
    {
        $stmt = $this->db->query("
            SELECT 
                t.id,
                t.product_name,
                t.product_type,
                t.total_amount,
                t.status,
                t.created_at,

                u.firstname,
                u.lastname,

                s.name as sales_name

            FROM transactions t

            LEFT JOIN users u ON u.id = t.user_id
            LEFT JOIN sales s ON s.id = t.sales_id

            ORDER BY t.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("
            UPDATE transactions
            SET status = ?, updated_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([$status, $id]);
    }

    public function findByIdWithUser($id)
    {
        $stmt = $this->db->prepare("
        SELECT 
            t.*,
            u.firstname,
            u.lastname,
            u.email
        FROM transactions t
        LEFT JOIN users u ON u.id = t.user_id
        WHERE t.id = ?
        LIMIT 1
    ");

        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}