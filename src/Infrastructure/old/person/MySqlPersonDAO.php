<?php

namespace App\Infrastructure\person;

use App\Application\person\PersonDAO;
use App\Entity\person\characteristic\CharacteristicBuilder;
use App\Entity\person\Identity;
use App\Entity\person\Person;
use App\Entity\person\PersonBuilder;
use App\Entity\school\degree\Degree;
use App\Entity\school\promotion\Promotion;
use App\Entity\school\School;
use App\Entity\school\SchoolAddress;
use App\Infrastructure\database\DatabaseConnection;
use DateTime;

/**
 * Mysql Person DAO
 */
class MySqlPersonDAO implements PersonDAO
{
    /**
     * @var DatabaseConnection $databaseConnection Database connection
     */
    private DatabaseConnection $databaseConnection;


    /**
     * @param DatabaseConnection $databaseConnection Database connection
     */
    public function __construct(DatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }


    /**
     * Get all the persons
     * @return array
     */
    public function getAllPeople(): array
    {

        $connection = $this->databaseConnection->getDatabase();

        $query = $connection->prepare(<<<SQL
                            SELECT Pe.*,
                                   Pr.*,
                                   D.*,
                                   Sc.*,
                                   C.id_characteristic,
                                   C.value,
                                   C.visibility,
                                   T.*
                            FROM Person Pe
                                LEFT JOIN Student St on Pe.id_person = St.id_person
                                LEFT JOIN Promotion Pr on St.id_promotion = Pr.id_promotion
                                LEFT JOIN Degree D on D.id_degree = Pr.id_degree
                                LEFT JOIN School Sc on Sc.id_school = Pr.id_school
                                LEFT JOIN Characteristic C on Pe.id_person = C.id_person
                                LEFT JOIN TypeCharacteristic T on C.id_network = T.id_network
                            ORDER BY Pe.id_person, characteristic_order
SQL
        );
        $query->execute();

        $users = [];

        $currentPerson = null;
        $buffer = [];

        while ($row = $query->fetch()) {
            if ($currentPerson === null) {
                $currentPerson = $row->id_person;
            }

            if ($currentPerson != $row->id_person) {
                $users[] = $this->buildPerson($buffer);
                $buffer = [];
                $currentPerson = $row->id_person;
            }

            $buffer[] = $row;
        }

        $users[] = $this->buildPerson($buffer);
        $query->closeCursor();
        return $users;
    }


    /**
     * Build a person
     * @param array $buffer Buffer of the person
     * @return Person
     */
    private function buildPerson(array $buffer): Person
    {

        $builder = PersonBuilder::aPerson()
            ->withId($buffer[0]->id_person)
            ->withIdentity(new Identity(
                $buffer[0]->first_name,
                $buffer[0]->last_name,
                $buffer[0]->picture,
                $buffer[0]->birthdate
            ))
            ->withBiography($buffer[0]->biography)
            ->withDescription($buffer[0]->description)
            ->withColor($buffer[0]->banner_color);

        $startYear = date("Y");
        $filterClosure = fn($row) => property_exists($row, 'id_degree') && $row->id_degree != null;
        $promotionBuffer = array_filter($buffer, $filterClosure);

        foreach ($promotionBuffer as $row) {
            $degree = new Degree(
                $row->id_degree,
                $row->degree_name,
                $row->level,
                $row->total_ects,
                $row->duration,
                $row->official
            );
            $school = new School(
                $row->id_school,
                $row->school_name,
                new SchoolAddress($row->address, $row->city),
                DateTime::createFromFormat('Y-m-d', $row->creation)
            );
            $promotion = new Promotion($row->id_promotion, $degree, $school, $row->year, $row->desc_promotion);
            $builder->addPromotion($promotion);
            $startYear = min($startYear, $row->year);
        }

        $filterClosure = fn($row) => property_exists($row, 'id_characteristic') && $row->id_characteristic != null;
        $characteristicsBuffer = array_filter($buffer, $filterClosure);

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


    /**
     * Get a person
     * @param Identity $identity Identity of the person
     * @return Person|null
     */
    public function getPerson(Identity $identity): ?Person
    {

        $connection = $this->databaseConnection->getDatabase();
        $query = $connection->prepare(<<<SQL
                        SELECT *
                        FROM Person
                        WHERE LOWER(first_name) = LOWER(:first_name)
                          AND LOWER(last_name) =  LOWER(:last_name)
                        LIMIT 1
SQL
        );

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


    /**
     * Get a person by id
     * @param int $id Id of the person
     * @return Person|null
     */
    public function getPersonById(int $id): ?Person
    {

        $connection = $this->databaseConnection->getDatabase();
        $query = $connection->prepare(<<<SQL
                                SELECT Pe.*,
                                       Pr.*,
                                       D.*,
                                       Sc.*,
                                       C.id_characteristic,
                                       C.value,
                                       C.visibility,
                                       T.*
                                FROM Person Pe
                                    LEFT JOIN Student St on Pe.id_person = St.id_person
                                    LEFT JOIN Promotion Pr on St.id_promotion = Pr.id_promotion
                                    LEFT JOIN Degree D on D.id_degree = Pr.id_degree
                                    LEFT JOIN School Sc on Sc.id_school = Pr.id_school
                                    LEFT JOIN Characteristic C on Pe.id_person = C.id_person
                                    LEFT JOIN TypeCharacteristic T on C.id_network = T.id_network
                                WHERE Pe.id_person = :id_person
                                ORDER BY characteristic_order
SQL
        );

        $query->execute(['id_person' => $id]);
        $buffer = [];

        while ($row = $query->fetch()) {
            $buffer[] = $row;
        }

        $person = null;

        if (!empty($buffer)) {
            $person = $this->buildPerson($buffer);
        }

        $query->closeCursor();
        $connection = null;
        return $person;
    }


    /**
     * Update de person
     * @param Person $person Person to update
     * @return void
     */
    public function updatePerson(Person $person): void
    {

        $connection = $this->databaseConnection->getDatabase();
        $query = $connection->prepare(<<<SQL
            UPDATE Person
            SET first_name = LOWER(:firstName), last_name = LOWER(:lastName), biography = :biography,
			    banner_color = :bannerColor, description = :description, picture = :picture
			WHERE id_person = :id
SQL
        );

        $query->execute([
            'firstName' => $person->getFirstName(),
            'lastName' => $person->getLastName(),
            'biography' => $person->getBiography(),
            'bannerColor' => $person->getColor(),
            'description' => $person->getDescription(),
            'picture' => $person->getPicture(),
            'id' => $person->getId()
        ]);
        $query->closeCursor();
        $connection = null;
    }


    /**
     * Get all identities
     * @return array
     */
    public function getAllIdentities(): array
    {

        $connection = $this->databaseConnection->getDatabase();

        $query = $connection->prepare(
            "SELECT first_name, last_name FROM Person"
        );
        $query->execute();

        $identities = [];

        while ($row = $query->fetch()) {
            $identities[] = new Identity($row->first_name, $row->last_name);
        }

        $query->closeCursor();
        return $identities;
    }


    /**
     * Get person by login
     * @param string $login Login of the person
     * @return Person|null
     */
    public function getPersonByLogin(string $login): ?Person
    {

        $connection = $this->databaseConnection->getDatabase();
        $query = $connection->prepare(<<<SQL
                                SELECT Pe.*,
                                       Pr.*,
                                       D.*,
                                       Sc.*,
                                       C.id_characteristic,
                                       C.value,
                                       C.visibility,
                                       T.*
                                FROM Person Pe
                                    LEFT JOIN Student St on Pe.id_person = St.id_person
                                    LEFT JOIN Promotion Pr on St.id_promotion = Pr.id_promotion
                                    LEFT JOIN Degree D on D.id_degree = Pr.id_degree
                                    LEFT JOIN School Sc on Sc.id_school = Pr.id_school
                                    LEFT JOIN Characteristic C on Pe.id_person = C.id_person
                                    LEFT JOIN TypeCharacteristic T on C.id_network = T.id_network
                                    LEFT JOIN Account A on Pe.id_person = A.id_person
                                WHERE LOWER(A.email) = LOWER(:login)
SQL
        );

        $query->execute(['login' => $login]);
        $buffer = [];

        while ($row = $query->fetch()) {
            $buffer[] = $row;
        }

        $query->closeCursor();
        $connection = null;
        return $this->buildPerson($buffer);
    }


    /**
     * Create a person
     * @param Person $person Person to create
     * @return int
     */
    public function createPerson(Person $person): int
    {

        $connection = $this->databaseConnection->getDatabase();
        $query = $connection->prepare(<<<SQL
                            INSERT INTO Person (first_name, last_name, biography, banner_color, description, picture)
                            VALUES (LOWER(:firstName), LOWER(:lastName), :biography,
                                    :bannerColor, :description, :picture)
SQL
        );

        $query->execute([
            'firstName' => $person->getFirstName(),
            'lastName' => $person->getLastName(),
            'biography' => $person->getBiography(),
            'bannerColor' => $person->getColor(),
            'description' => $person->getDescription(),
            'picture' => $person->getPicture()
        ]);

        $idPerson = $connection->lastInsertId();

        $query = $connection->prepare(<<<SQL
                                SELECT id_promotion
                                FROM Promotion
                                WHERE year = :start_year
                                  AND desc_promotion = 'Première année'
SQL
        );
        $query->execute(['start_year' => $person->getStartYear()]);

        if ($row = $query->fetch()) {
            $idPromotion = $row->id_promotion;
        } else {
            $query = $connection->prepare(<<<SQL
                                INSERT INTO Promotion (year, id_degree, id_school, desc_promotion, speciality)
                                VALUES (:start_year,
                                        (SELECT id_degree FROM Degree WHERE degree_name = :degree_name),
                                        (SELECT id_school FROM School WHERE school_name = 'IUT Lyon 1'),
                                        'Première année',
                                        'Informatique')
SQL
            );
            $query->execute([
                'start_year' => $person->getStartYear(),
                'degree_name' => $person->getStartYear() < 2021 ? 'DUT' : 'BUT'
            ]);
            $idPromotion = $connection->lastInsertId();
        }

        $query = $connection->prepare(<<<SQL
                                INSERT INTO Student (id_person, id_promotion)
                                VALUES (:id_person, :id_promotion)
SQL
        );

        $query->execute([
            'id_person' => $idPerson,
            'id_promotion' => $idPromotion
        ]);

        $query->closeCursor();
        $connection = null;

        return $idPerson;
    }


    /**
     * Delete a person
     * @param Person $person Person to delete
     * @return void
     */
    public function deletePerson(Person $person): void
    {

        $connection = $this->databaseConnection->getDatabase();

        $query = $connection->prepare("DELETE FROM Person WHERE id_person = :id");
        $query->execute(['id' => $person->getId()]);
        $query->closeCursor();
        $connection = null;
    }
}
