<?php

namespace App\infrastructure\accountService;

use App\application\login\AccountDAO;
use App\infrastructure\database\DatabaseConnection;
use App\model\account\Account;
use App\model\account\Password;
use App\model\account\Privilege;
use App\model\account\PrivilegeType;
use App\model\person\Identity;
use App\model\person\PersonBuilder;
use App\model\school\School;

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

		$query = $connection->prepare("INSERT INTO Privilege (id_account, id_school, privilege_name) VALUES ((SELECT id_account FROM Account WHERE email = :email), 1, 'STUDENT')");
		$query->execute(['email' => $account->getLogin()]);

		$connection = null;
		$query->closeCursor();
	}

    public function existsAccount(string $email): bool {
		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare("SELECT * FROM Account WHERE email = :email");
		$query->execute(['email' => $email]);
		$result = $query->fetch();

		$connection = null;
		return (bool)$result;
	}

    public function getSimpleAccount(mixed $username): Account {
        $connection = $this->databaseConnection->getDatabase();

        $query = $connection->prepare("SELECT * FROM Account A LEFT JOIN Privilege P on A.id_account = P.id_account WHERE email = :login");
        $query->execute(['login' => $username]);

        $school = School::emptySchool();
        $person = PersonBuilder::aPerson()->build();
        $privileges = [];
        $id = 0;
        $email = '';
        $password = new Password('');

        while ($row = $query->fetch()) {
            $privileges[] = new Privilege($school, PrivilegeType::fromString($row->privilege_name));
            $id = $row->id_account;
            $email = $row->email;
            $password = new Password($row->password);
		}

		$account = new Account($id, $email, $person, $password, ...$privileges);

		$query->closeCursor();
		$connection = null;
		return $account;

	}

	public function existsAccountByIdentity(Identity $identity): bool {
		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare("SELECT * FROM Account
												JOIN Person P on P.id_person = Account.id_person
											 	WHERE first_name = :first_name AND last_name = :last_name");
		$query->execute(['first_name' => $identity->getFirstName(), 'last_name' => $identity->getLastName()]);
		$result = $query->fetch();

		$connection = null;
		return (bool)$result;
	}

	public function createTemporaryAccount(Account $account, string $link): void {
		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare("INSERT INTO TemporaryAccount (email, password, id_person, link) VALUES (:email, :password, :person, :link)");
		$query->execute([
			'email' => $account->getLogin(),
			'password' => $account->getHashedPassword(),
			'person' => $account->getPersonId(),
			'link' => $link
		]);

		$connection = null;
		$query->closeCursor();
	}

	public function getTemporaryAccountByToken(string $token): Account {
		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare("SELECT * FROM TemporaryAccount WHERE link = :token");
		$query->execute(['token' => $token]);

		$account = new Account(-1, '', PersonBuilder::aPerson()->build(), new Password(''));

		if ($result = $query->fetch()) {
			$account = new Account($result->id_account, $result->email, PersonBuilder::aPerson()->withId($result->id_person)->build(), new Password($result->password));
		}

		$connection = null;
		$query->closeCursor();
		return $account;
	}

	public function deleteTemporaryAccount(Account $account): void {
		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare("DELETE FROM TemporaryAccount WHERE id_account = :id");
		$query->execute(['id' => $account->getId()]);

		$connection = null;
		$query->closeCursor();
	}

}