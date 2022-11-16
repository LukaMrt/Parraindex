<?php

namespace App\infrastructure\accountService;

use App\application\login\AccountDAO;
use App\infrastructure\database\DatabaseConnection;
use App\model\account\Account;
use App\model\account\Password;
use App\model\person\Identity;
use App\model\person\Person;
use App\model\person\PersonBuilder;

class MysqlAccountDAO implements AccountDAO {

    private DatabaseConnection $databaseConnection;

    public function __construct(DatabaseConnection $databaseConnection) {
        $this->databaseConnection = $databaseConnection;
    }

    public function getAccountPassword(string $login): Password {
        $connection = $this->databaseConnection->getDatabase();

        $query = $connection->prepare("SELECT password FROM Account WHERE email = :login LIMIT 1");
        $query->execute(['login' => $login]);
        $result = $query->fetch();
		$password = new Password('');

		if ($result) {
			$password = new Password($result->password);
		}

		$query->closeCursor();
        $connection = null;
        return $password;
    }

    public function createAccount(Account $account): void {
        $connection = $this->databaseConnection->getDatabase();

        $query = $connection->prepare("INSERT INTO Account (email, password, id_person) VALUES (:email, :password, :person)");
		$query->execute([
			'email' => $account->getLogin(),
			'password' => $account->getHashedPassword(),
			'person' => $account->getPersonId()
		]);

		$connection = null;
    }

	public function existsAccount(string $email): bool {
		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare("SELECT * FROM Account WHERE email = :email");
		$query->execute(['email' => $email]);
		$result = $query->fetch();

		$connection = null;
		return (bool)$result;
	}

}