<?php

namespace App\infrastructure;

use App\application\UserDAO;
use App\infrastructure\database\DatabaseConnection;
use App\model\user\User;

class MySqlUserDAO implements UserDAO {

    private DatabaseConnection $databaseConnection;

    public function __construct(DatabaseConnection $databaseConnection) {
        $this->databaseConnection = $databaseConnection;
    }

    function getUsers(): array {

        $connection = $this->databaseConnection->getDatabase();

        $result = $connection->query("SELECT * FROM Person");

        $users = array();

        while ($row = $result->fetch()) {
            $users[] = new User($row->lastName);
        }

        return $users;
    }

}