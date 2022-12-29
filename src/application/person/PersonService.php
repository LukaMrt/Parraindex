<?php

namespace App\application\person;

use App\model\person\Identity;
use App\model\person\Person;
use App\model\person\PersonBuilder;

use App\application\login\SessionManager;

class PersonService {

    private PersonDAO $personDAO;
    private SessionManager $sessionManager;

    public function __construct(PersonDAO $personDAO, SessionManager $sessionManager) {
        $this->personDAO = $personDAO;
        $this->sessionManager = $sessionManager;
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
			->withIdentity(new Identity($parameters['first_name'], $parameters['last_name'], $parameters['picture']))
			->withBiography($parameters['biography'])
            ->withDescription($parameters['description'])
            ->withColor($parameters['color'])
			->build();

        if ($this->sessionManager->get('user')->getId() === $person->getId()) {
            $this->sessionManager->set('user', $person);
        }

		$this->personDAO->updatePerson($person);
	}

	public function getPersonByIdentity(Identity $identity): ?Person {
		return $this->personDAO->getPerson($identity);
	}

    public function createPerson(array $parameters): int {
        $person = PersonBuilder::aPerson()
            ->withIdentity(new Identity($parameters['first_name'], $parameters['last_name'], $parameters['picture']))
            ->withBiography($parameters['biography'])
            ->withDescription($parameters['description'])
            ->withColor($parameters['color'])
            ->build();

        return $this->personDAO->createPerson($person);
    }

    public function deletePerson(Person $person): void {
        $this->personDAO->deletePerson($person);
    }

	public function addPerson(Person $person): void {
		$this->personDAO->addPerson($person);
	}

	public function removePerson(int $id): void {
		$this->personDAO->removePerson($id);
	}

}