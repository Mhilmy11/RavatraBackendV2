<?php

declare(strict_types=1);

final class Database
{
    /**
     * Singleton Instance
     */
    private static ?Database $instance = null;

    /**
     * PDO Connection
     */
    private PDO $connection;

    /**
     * Prevent direct object creation.
     */
    private function __construct()
    {
        $config = require __DIR__ . '/../config/database.php';

        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {

            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

        } catch (PDOException $e) {

            throw new RuntimeException(
                'Database connection failed : ' . $e->getMessage()
            );

        }
    }

    /**
     * Get Singleton Instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get PDO Connection
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Begin Database Transaction
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit Transaction
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Rollback Transaction
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }
}