<?php

namespace App\infrastructure\person;

use App\application\person\PersonDAO;
use App\infrastructure\database\DatabaseConnection;
use App\model\person\Identity;
use App\model\person\Person;
use App\model\person\PersonBuilder;

class MySqlPersonDAO implements PersonDAO {

	private DatabaseConnection $databaseConnection;

	public function __construct(DatabaseConnection $databaseConnection) {
		$this->databaseConnection = $databaseConnection;
	}

	function getAllPeople(): array {

		$connection = $this->databaseConnection->getDatabase();
		$result = $connection->query("SELECT * FROM Person");

		$users = array();

		while ($row = $result->fetch()) {
			$users[] = $this->buildPerson($row);
		}

		$result->closeCursor();
		$connection = null;

		return $users;
	}

	public function buildPerson(mixed $row): Person {

		$builder = PersonBuilder::aPerson()
			->withId($row->id_person)
			->withIdentity(new Identity($row->first_name, $row->last_name, $row->picture, $row->birthdate))
			->withBiography($row->biography);

		return $builder->build();
	}

	public function getPerson(Identity $identity): ?Person {

		$connection = $this->databaseConnection->getDatabase();
		$query = $connection->prepare("SELECT * FROM Person WHERE first_name = :first_name AND last_name = :last_name LIMIT 1");

		$query->execute([
			'first_name' => $identity->getFirstName(),
			'last_name' => $identity->getLastName()
		]);
		$result = $query->fetch();

		$person = null;

		if ($result) {
			$person = $this->buildPerson($result);
		}

		$query->closeCursor();
		$connection = null;
		return $person;
	}

}