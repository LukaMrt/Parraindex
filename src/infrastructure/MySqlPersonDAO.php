<?php

namespace App\infrastructure;

use App\application\UserDAO;
use App\infrastructure\database\DatabaseConnection;
use App\model\person\Person;

class MySqlPersonDAO implements UserDAO {

    private DatabaseConnection $databaseConnection;

    public function __construct(DatabaseConnection $databaseConnection) {
        $this->databaseConnection = $databaseConnection;
    }

    function getUsers(): array {

        $connection = $this->databaseConnection->getDatabase();

        $result = $connection->query("SELECT * FROM Person");

        $users = array();

        while ($row = $result->fetch()) {
            $users[] = new Person($row->lastName);
        }

        return $users;
    }

}