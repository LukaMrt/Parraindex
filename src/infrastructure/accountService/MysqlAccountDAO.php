<?php

namespace App\infrastructure\accountService;

use App\application\login\AccountDAO;
use App\infrastructure\database\DatabaseConnection;
use App\model\account\Password;

class MysqlAccountDAO implements AccountDAO {

    private DatabaseConnection $databaseConnection;

    public function __construct(DatabaseConnection $databaseConnection) {
        $this->databaseConnection = $databaseConnection;
    }

    public function getAccountPassword(string $login): Password {
        $connection = $this->databaseConnection->getDatabase();

        $query = $connection->prepare("SELECT * FROM Account WHERE email = :login");
        $query->execute(['login' => $login]);
        $result = $query->fetch();

        $connection = null;
        return new Password($result != null ? $result->password : '');
    }

    public function createAccount(string $email, string $password, string $name, string $firstname): void {
        $connection = $this->databaseConnection->getDatabase();
        $sql = $connection->prepare("INSERT INTO Account (email, password, id_person) 
                                                VALUES (:email, :password,
                                                        (SELECT P.id_person FROM Person P WHERE last_name = :name AND first_name = :firstname)
                                                        )");
        $sql->bindParam(':email', $email);
        $password = new  Password($password);
        $password->hashPassword(PASSWORD_DEFAULT);
        $password = $password->getPassword();
        $sql->bindParam(':password', $password);
        $sql->bindParam(':name', $name);
        $sql->bindParam(':firstname', $firstname);
        $sql->execute();
        $connection = null;
    }

}