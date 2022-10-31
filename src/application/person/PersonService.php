<?php

namespace App\application\person;

class PersonService {

    private PersonDAO $personDAO;

    public function __construct(PersonDAO $personDAO) {
        $this->personDAO = $personDAO;
    }

    public function getAllPeople(): array {
        return $this->personDAO->getAllPeople();
    }

}