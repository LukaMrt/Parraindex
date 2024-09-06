<?php

namespace App\Infrastructure\database;

use PDO;

/**
 * Database Connection
 */
class DatabaseConnection
{
    /**
     * @var PDO PDO instance
     */
    private PDO $database;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->connect();
    }


    /**
     * Connect to database using environment variables
     * @return void
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

        $this->database = new PDO(
            "$driver:host=$host; dbname=$database; port=$port; charset=utf8",
            $username,
            $password
        );

        $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        $this->database->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
    }


    /**
     * Get PDO instance
     * @return PDO PDO instance
     */
    public function getDatabase(): PDO
    {
        return $this->database;
    }
}
