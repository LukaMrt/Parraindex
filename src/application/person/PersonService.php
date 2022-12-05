<?php

namespace App\application\person;

use App\model\person\Person;

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

	public function updatePerson(array $parameters): void {
		$this->personDAO->updatePerson($parameters);
	}

}