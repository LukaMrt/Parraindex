<?php

namespace App\infrastructure\sponsor;

use App\application\sponsor\SponsorDAO;
use App\infrastructure\database\DatabaseConnection;
use App\model\person\characteristic\CharacteristicBuilder;
use App\model\person\Identity;
use App\model\person\Person;
use App\model\person\PersonBuilder;
use PDOStatement;

class MySqlSponsorDAO implements SponsorDAO {

    private DatabaseConnection $databaseConnection;

    public function __construct(DatabaseConnection $databaseConnection) {
        $this->databaseConnection = $databaseConnection;
    }

    public function getPersonFamily(int $personId): array {

        $connection = $this->databaseConnection->getDatabase();

        $personQuery = $connection->prepare(<<<SQL
            SELECT P.*, C.id_characteristic, C.value, C.visibility, T.*
            FROM Person P
            LEFT JOIN Characteristic C on P.id_person = C.id_person
            LEFT JOIN TypeCharacteristic T on C.id_network = T.id_network
            WHERE P.id_person = :id
SQL
        );

        $godFathersQuery = $connection->prepare(<<<SQL
            SELECT P.*
            FROM Person P
            JOIN Sponsor S on P.id_person = S.id_godfather
            WHERE S.id_godson = :id
SQL
        );

        $godChildrenQuery = $connection->prepare(<<<SQL
            SELECT P.*
            FROM Person P
            JOIN Sponsor S on P.id_person = S.id_godson
            WHERE S.id_godfather = :id
SQL
        );

        $personQuery->execute(['id' => $personId]);
        $godFathersQuery->execute(['id' => $personId]);
        $godChildrenQuery->execute(['id' => $personId]);

        $person = $this->buildPeople($personQuery)[0];
        $godFathers = $this->buildPeople($godFathersQuery);
        $godChildren = $this->buildPeople($godChildrenQuery);

        $personQuery->closeCursor();
        $godFathersQuery->closeCursor();
        $godChildrenQuery->closeCursor();
        $connection = null;
        return [
            'person' => $person,
            'godFathers' => $godFathers,
            'godChildren' => $godChildren
        ];
    }

    private function buildPerson(array $buffer): Person {

        $characteristics = array();
        $characteristicsBuffer = array_filter($buffer, fn($row) => property_exists($row, 'id_characteristic') && $row->id_characteristic != null);

        foreach ($characteristicsBuffer as $row) {
            $characteristics[] = (new CharacteristicBuilder())
                ->withId($row->id_characteristic)
                ->withTitle($row->title)
                ->withType($row->type)
                ->withUrl($row->url)
                ->withImage($row->image)
                ->withVisibility($row->visibility)
                ->withValue($row->value)
                ->build();
        }

        return PersonBuilder::aPerson()
            ->withId($buffer[0]->id_person)
            ->withIdentity(new Identity($buffer[0]->first_name, $buffer[0]->last_name, $buffer[0]->picture, $buffer[0]->birthdate))
            ->withBiography($buffer[0]->biography)
            ->withCharacteristics($characteristics)
            ->build();

    }

    private function buildPeople(bool|PDOStatement $query): array {

        $people = array();

        $currentPerson = null;
        $buffer = array();

        while ($row = $query->fetch()) {

            if ($currentPerson === null) {
                $currentPerson = $row->id_person;
            }

            if ($currentPerson != $row->id_person) {
                $people[] = $this->buildPerson($buffer);
                $buffer = array();
                $currentPerson = $row->id_person;
            }

            $buffer[] = $row;
        }

        $people[] = $this->buildPerson($buffer);
        return $people;
    }

}