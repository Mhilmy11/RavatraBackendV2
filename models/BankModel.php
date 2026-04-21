<?php

require_once __DIR__ . '/../core/Model.php';

class BankModel extends Model
{
    protected $table = 'banks';

    public function getAll()
    {
        $stmt = $this->db->query("
            SELECT id, bank_name, account_number, account_name
            FROM {$this->table}
            ORDER BY id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}