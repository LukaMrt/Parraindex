<?php

namespace App\application\person;

use App\model\person\Identity;
use App\model\person\Person;
use App\model\person\PersonBuilder;

class PersonService {

    private PersonDAO $personDAO;

    public function __construct(PersonDAO $personDAO) {
        $this->personDAO = $personDAO;
    }

    public function getAllPeople(): array {
        return $this->personDAO->getAllPeople();
    }

    public function getPersonById(int $id): ?Person {
        return $this->personDAO->getPersonById($id);
    }

    public function getPersonByLogin(string $login): ?Person {
        return $this->personDAO->getPersonByLogin($login);
    }

	public function updatePerson(array $parameters): void {
		$person = PersonBuilder::aPerson()
			->withId($parameters['id'])
			->withIdentity(new Identity($parameters['first_name'], $parameters['last_name']))
			->withBiography($parameters['biography'])
			->build();

		$this->personDAO->updatePerson($person);
	}

	public function getPersonByIdentity(Identity $identity): ?Person {
		return $this->personDAO->getPerson($identity);
	}

	public function addPerson(Person $person): void {
		$this->personDAO->addPerson($person);
	}

	public function removePerson(int $id): void {
		$this->personDAO->removePerson($id);
	}

}