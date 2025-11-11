<?php
namespace System\Database\DBConnection;

use PDO;
use PDOException;

class DBConnection
{
    private static ?DBConnection $instance = null;
    private ?PDO $connection = null;

    private function __construct()
    {
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO(
                'mysql:host=' . DBHOST . ';dbname=' . DBNAME,
                DBUSER,
                DBPASS,
                $options
            );
        } catch (PDOException $e) {
            throw new PDOException("Error in database connection: " . $e->getMessage());
        }
    }

    public static function getInstance(): DBConnection
    {
        if (self::$instance === null) {
            self::$instance = new DBConnection();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function newInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    private function __clone() {}
    private function __wakeup() {}
}
