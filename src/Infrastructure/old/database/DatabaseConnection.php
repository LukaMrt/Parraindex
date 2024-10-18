<?php

declare(strict_types=1);

namespace App\Infrastructure\old\database;

use PDO;

/**
 * Database Connection
 */
class DatabaseConnection
{
    /**
     * @var PDO PDO instance
     */
    private PDO $pdo;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->connect();
    }


    /**
     * Connect to database using environment variables
     */
    private function connect(): void
    {

        [
            'DRIVER' => $driver,
            'HOST' => $host,
            'PORT' => $port,
            'DATABASE' => $database,
            'USERNAME' => $username,
            'PASSWORD' => $password,
        ] = $_ENV;

        $this->pdo = new PDO(
            sprintf('%s:host=%s; dbname=%s; port=%s; charset=utf8', $driver, $host, $database, $port),
            $username,
            $password
        );

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        $this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
    }


    /**
     * Get PDO instance
     * @return PDO PDO instance
     */
    public function getDatabase(): PDO
    {
        return $this->pdo;
    }
}
