<?php

class Database
{
    public static function connect()
    {
        try {
            $host = env('DB_HOST');
            $db = env('DB_NAME');
            $user = env('DB_USER');
            $pass = env('DB_PASS');

            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;

        } catch (PDOException $e) {
            die("DB ERROR: " . $e->getMessage());
        }
    }
}