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
								 LEFT JOIN TypeCharacteristic T on C.id_network = T.id_network");
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
            ->withBiography($buffer[0]->biography);

        $promotionBuffer = array_filter($buffer, fn($row) => $row->id_degree != null);

        foreach ($promotionBuffer as $row) {
            $degree = new Degree($row->id_degree, $row->degree_name, $row->level, $row->total_ects, $row->duration, $row->official);
            $school = new School($row->id_school, $row->school_name, new SchoolAddress($row->address, $row->city), DateTime::createFromFormat('Y-m-d', $row->creation));
            $promotion = new Promotion($row->id_promotion, $degree, $school, $row->year, $row->description);
            $builder->addPromotion($promotion);
        }

        $characteristicsBuffer = array_filter($buffer, fn($row) => $row->id_characteristic != null);

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

    public function getPersonById(int $id): ?Person {

        $connection = $this->databaseConnection->getDatabase();
        $query = $connection->prepare("SELECT * FROM Person WHERE id_person = :id_person LIMIT 1");

        $query->execute(['id_person' => $id]);
        $result = $query->fetch();

        $person = null;

        if ($result) {
            $person = PersonBuilder::aPerson()
                ->withId($result->id_person)
                ->withIdentity(new Identity($result->first_name, $result->last_name, $result->picture, $result->birthdate))
                ->withBiography($result->biography)
                ->build();
        }

        $query->closeCursor();
        $connection = null;
        return $person;
    }

}
