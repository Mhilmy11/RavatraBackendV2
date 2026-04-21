<?php

require_once __DIR__ . '/../core/Model.php';

class UserModel extends Model
{
    protected $table = 'users';

    public function findByEmailOrPhone($email, $phone)
    {
        $stmt = $this->db->prepare("
        SELECT * FROM users
        WHERE email = ? OR phone = ?
        LIMIT 1
    ");

        $stmt->execute([$email, $phone]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
        INSERT INTO users (firstname, lastname, email, phone, company, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");

        $stmt->execute([
            $data['firstname'],
            $data['lastname'],
            $data['email'],
            $data['phone'],
            $data['company']
        ]);

        return $this->db->lastInsertId();
    }

    public function getAllUsers()
    {
        $query = "SELECT id, firstname, lastname, company, email, phone, role, created_at 
              FROM users 
              ORDER BY id DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByEmail($email)
    {
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}