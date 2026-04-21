<?php

require_once __DIR__ . '/../core/Model.php';

class SalesModel extends Model
{
    protected $table = 'sales';

    public function getAll()
    {
        $stmt = $this->db->query("
        SELECT 
            id,
            name,
            phone
        FROM sales
        ORDER BY name ASC
    ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}