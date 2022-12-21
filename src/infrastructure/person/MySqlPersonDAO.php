<?php

namespace App\infrastructure\person;

use App\application\person\PersonDAO;
use App\infrastructure\database\DatabaseConnection;
use App\model\person\characteristic\CharacteristicBuilder;
use App\model\person\Identity;
use App\model\person\Person;
use App\model\person\PersonBuilder;
use App\model\school\degree\Degree;
use App\model\school\promotion\Promotion;
use App\model\school\School;
use App\model\school\SchoolAddress;
use DateTime;

class MySqlPersonDAO implements PersonDAO {

    private DatabaseConnection $databaseConnection;

    public function __construct(DatabaseConnection $databaseConnection) {
        $this->databaseConnection = $databaseConnection;
    }

    public function getAllPeople(): array {

		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare(
			"SELECT Pe.*, Pr.*, D.*, Sc.*, C.id_characteristic, C.value, C.visibility, T.*
						FROM Person Pe
								 LEFT JOIN Student St on Pe.id_person = St.id_person
								 LEFT JOIN Promotion Pr on St.id_promotion = Pr.id_promotion
								 LEFT JOIN Degree D on D.id_degree = Pr.id_degree
								 LEFT JOIN School Sc on Sc.id_school = Pr.id_school
								 LEFT JOIN Characteristic C on Pe.id_person = C.id_person
								 LEFT JOIN TypeCharacteristic T on C.id_network = T.id_network
						ORDER BY Pe.id_person");
		$query->execute();

		$users = array();

		$currentPerson = null;
		$buffer = array();

		while ($row = $query->fetch()) {

			if ($currentPerson === null) {
				$currentPerson = $row->id_person;
			}

			if ($currentPerson != $row->id_person) {
				$users[] = $this->buildPerson($buffer);
				$buffer = array();
				$currentPerson = $row->id_person;
			}

			$buffer[] = $row;
		}

        $users[] = $this->buildPerson($buffer);
        $query->closeCursor();
        return $users;
    }

    private function buildPerson(array $buffer): Person {

		$builder = PersonBuilder::aPerson()
			->withId($buffer[0]->id_person)
			->withIdentity(new Identity($buffer[0]->first_name, $buffer[0]->last_name, $buffer[0]->picture, $buffer[0]->birthdate))
			->withBiography($buffer[0]->biography)
			->withDescription($buffer[0]->description)
			->withColor($buffer[0]->banner_color);

		$startYear = date("Y");
		$promotionBuffer = array_filter($buffer, fn($row) => property_exists($row, 'id_degree') && $row->id_degree != null);

		foreach ($promotionBuffer as $row) {
			$degree = new Degree($row->id_degree, $row->degree_name, $row->level, $row->total_ects, $row->duration, $row->official);
			$school = new School($row->id_school, $row->school_name, new SchoolAddress($row->address, $row->city), DateTime::createFromFormat('Y-m-d', $row->creation));
			$promotion = new Promotion($row->id_promotion, $degree, $school, $row->year, $row->desc_promotion);
			$builder->addPromotion($promotion);
			$startYear = min($startYear, $row->year);
		}

		$characteristicsBuffer = array_filter($buffer, fn($row) => property_exists($row, 'id_characteristic') && $row->id_characteristic != null);

		foreach ($characteristicsBuffer as $row) {
			$builder->addCharacteristic((new CharacteristicBuilder())
				->withId($row->id_characteristic)
                ->withTitle($row->title)
                ->withType($row->type)
                ->withUrl($row->url)
                ->withImage($row->image)
                ->withVisibility($row->visibility)
                ->withValue($row->value)
                ->build());
        }

        return $builder->withStartYear($startYear)->build();
    }

    public function getPerson(Identity $identity): ?Person {

        $connection = $this->databaseConnection->getDatabase();
        $query = $connection->prepare("SELECT * FROM Person WHERE LOWER(first_name) = :first_name AND LOWER(last_name) = :last_name LIMIT 1");

        $query->execute([
            'first_name' => $identity->getFirstName(),
            'last_name' => $identity->getLastName()
        ]);
        $result = $query->fetch();

        $person = null;

        if ($result) {
            $person = $this->buildPerson([$result]);
        }

        $query->closeCursor();
        $connection = null;
        return $person;
    }

    public function getPersonById(int $id): ?Person {

		$connection = $this->databaseConnection->getDatabase();
		$query = $connection->prepare("SELECT Pe.*, Pr.*, D.*, Sc.*, C.id_characteristic, C.value, C.visibility, T.*
													FROM Person Pe
         											LEFT JOIN Student St on Pe.id_person = St.id_person
													LEFT JOIN Promotion Pr on St.id_promotion = Pr.id_promotion
													LEFT JOIN Degree D on D.id_degree = Pr.id_degree
													LEFT JOIN School Sc on Sc.id_school = Pr.id_school
													LEFT JOIN Characteristic C on Pe.id_person = C.id_person
													LEFT JOIN TypeCharacteristic T on C.id_network = T.id_network
         											WHERE Pe.id_person = :id_person");

		$query->execute(['id_person' => $id]);
		$buffer = array();

		while ($row = $query->fetch()) {
			$buffer[] = $row;
		}

		$person = null;

		if (count($buffer) > 0) {
			$person = $this->buildPerson($buffer);
		}

		$query->closeCursor();
		$connection = null;
		return $person;
	}

	public function updatePerson(Person $person) {

		$connection = $this->databaseConnection->getDatabase();
		$query = $connection->prepare("UPDATE Person SET first_name = :firstName, last_name = :lastName, biography = :biography WHERE id_person = :id");

		$query->execute([
			'firstName' => $person->getFirstName(),
			'lastName' => $person->getLastName(),
			'biography' => $person->getBiography(),
			'id' => $person->getId()
		]);
		$query->closeCursor();
		$connection = null;
	}

	public function getAllIdentities(): array {

		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare(
			"SELECT first_name, last_name FROM Person");
		$query->execute();

		$identities = array();

		while ($row = $query->fetch()) {
			$identities[] = new Identity($row->first_name, $row->last_name);
		}

		$query->closeCursor();
		return $identities;
	}

	public function getPersonByLogin(string $login): ?Person {

		$connection = $this->databaseConnection->getDatabase();
		$query = $connection->prepare("SELECT Pe.*, Pr.*, D.*, Sc.*, C.id_characteristic, C.value, C.visibility, T.*
													FROM Person Pe
         											LEFT JOIN Student St on Pe.id_person = St.id_person
													LEFT JOIN Promotion Pr on St.id_promotion = Pr.id_promotion
													LEFT JOIN Degree D on D.id_degree = Pr.id_degree
													LEFT JOIN School Sc on Sc.id_school = Pr.id_school
													LEFT JOIN Characteristic C on Pe.id_person = C.id_person
													LEFT JOIN TypeCharacteristic T on C.id_network = T.id_network
													LEFT JOIN Account A on Pe.id_person = A.id_person
         											WHERE A.email = :login");

		$query->execute(['login' => $login]);
		$buffer = array();

		while ($row = $query->fetch()) {
			$buffer[] = $row;
		}

		$query->closeCursor();
		$connection = null;
		return $this->buildPerson($buffer);
	}

	public function addPerson(Person $person): void {

		$connection = $this->databaseConnection->getDatabase();
		$query = $connection->prepare("INSERT INTO Person (first_name, last_name, biography) VALUES (:firstName, :lastName, :biography)");

		$query->execute([
			'firstName' => $person->getFirstName(),
			'lastName' => $person->getLastName(),
			'biography' => $person->getBiography()
		]);

		$idPerson = $connection->lastInsertId();

		$query = $connection->prepare("SELECT id_promotion FROM Promotion WHERE year = :start_year AND desc_promotion = 'Première année'");
		$query->execute(['start_year' => $person->getStartYear()]);

		if ($row = $query->fetch()) {
			$id_promotion = $row->id_promotion;
		} else {
			$query = $connection->prepare("INSERT INTO Promotion (year, id_degree, id_school, desc_promotion, speciality) VALUES (:start_year, (SELECT id_degree FROM Degree WHERE degree_name = :degree_name), (SELECT id_school FROM School WHERE school_name = 'IUT Lyon 1'), 'Première année', 'Informatique')");
			$query->execute([
				'start_year' => $person->getStartYear(),
				'degree_name' => $person->getStartYear() < 2021 ? 'DUT' : 'BUT'
			]);
			$id_promotion = $connection->lastInsertId();
		}

		$query = $connection->prepare("INSERT INTO Student (id_person, id_promotion) VALUES (:id_person, :id_promotion)");

		$query->execute([
			'id_person' => $idPerson,
			'id_promotion' => $id_promotion
		]);

		$query->closeCursor();
		$connection = null;
	}

	public function removePerson(int $id) {

		$connection = $this->databaseConnection->getDatabase();
		$query = $connection->prepare("DELETE FROM Person WHERE id_person = :id");
		$query->execute(['id' => $id]);

		$query->closeCursor();
		$connection = null;
	}

}
