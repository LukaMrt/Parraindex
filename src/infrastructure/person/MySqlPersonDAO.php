<?php

namespace App\infrastructure\person;

use App\application\person\PersonDAO;
use App\infrastructure\database\DatabaseConnection;
use App\model\person\Biography;
use App\model\person\Identity;
use App\model\person\Person;
use App\model\person\PersonBuilder;
use App\model\utils\Id;
use App\model\utils\Image;
use DateTime;

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

        return $users;
    }

    /**
     * @param mixed $row
     * @return Person
     */
    public function buildPerson(mixed $row): Person {

        $builder = PersonBuilder::aPerson()
            ->withId(new Id($row->id_person))
            ->withName(new Identity($row->first_name, $row->last_name));

        if ($row->birthdate != null) {
            $builder->withBirthDate(DateTime::createFromFormat("Y-m-d", $row->birthdate));
        }

        if ($row->biography != null) {
            $builder->withBiography(new Biography($row->biography));
        }

        if ($row->picture != null) {
            $builder->withPicture(new Image($row->picture));
        }

        return $builder->build();
    }

}