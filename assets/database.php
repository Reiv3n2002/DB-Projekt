<?php
class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        // Datenbankverbindungsdaten
        $host = '94.231.94.130';
        $dbname = 'DB_Projekt';
        $username = 'DB-Projekt';
        $password = '1&G9o6uu6';

        try {
            $this->pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die('Verbindungsfehler: ' . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    public function prepare($query)
    {
        return $this->pdo->prepare($query);
    }

    public function getPDO()
    {
        return $this->pdo;
    }
}
