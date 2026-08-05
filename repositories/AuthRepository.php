<?php

declare(strict_types=1);

final class AuthRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function emailExists(string $email): bool
    {
        $sql = "
            SELECT id
            FROM users
            WHERE email = :email
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':email', $email);

        $stmt->execute();

        return (bool) $stmt->fetch();
    }

    public function getLastUserCode(): ?string
    {
        $sql = "
            SELECT user_code
            FROM users
            ORDER BY id DESC
            LIMIT 1
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchColumn() ?: null;
    }

    public function register(array $data): bool
    {
        $sql = "
            INSERT INTO users
            (
                user_code,
                firstname,
                lastname,
                company,
                email,
                phone,
                password,
                role,
                status,
                created_at,
                updated_at
            )
            VALUES
            (
                :user_code,
                :firstname,
                :lastname,
                :company,
                :email,
                :phone,
                :password,
                'USER',
                'ACTIVE',
                NOW(),
                NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':user_code' => $data['user_code'],
            ':firstname' => $data['firstname'],
            ':lastname' => $data['lastname'],
            ':company' => $data['company'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':password' => $data['password'],
        ]);
    }

    public function findByEmail(string $email): ?array
    {
        $sql = "
        SELECT
            id,
            user_code,
            firstname,
            lastname,
            company,
            email,
            phone,
            password,
            role,
            status
        FROM users
        WHERE email = :email
        LIMIT 1
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':email', $email);

        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findByUserCode(string $userCode): ?array
    {
        $sql = "
        SELECT
            user_code,
            firstname,
            lastname,
            company,
            email,
            phone,
            role,
            status,
            created_at
        FROM users
        WHERE user_code = :user_code
        LIMIT 1
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'user_code' => $userCode
        ]);

        $user = $stmt->fetch();

        return $user ?: null;
    }
}