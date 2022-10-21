<?php

namespace App\infrastructure\database;

use App\model\database\DatabaseCredentials;
use PDO;

class DatabaseConnection {

    private DatabaseCredentials $databaseCredentials;
    private PDO $database;

    public function __construct(DatabaseCredentials $databaseCredentials) {
        $this->databaseCredentials = $databaseCredentials;
        $this->connect();
    }

    private function connect(): void {

        $this->database = new PDO(
            $this->databaseCredentials->getDsn(),
            $this->databaseCredentials->getUsername(),
            $this->databaseCredentials->getPassword(),
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_STRINGIFY_FETCHES => false
            )
        );

    }

    public function getDatabase(): PDO {
        return $this->database;
    }

}