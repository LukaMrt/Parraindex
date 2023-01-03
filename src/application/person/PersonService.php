<?php

namespace App\application\person;

use App\application\login\SessionManager;
use App\model\person\Identity;
use App\model\person\Person;
use App\model\person\PersonBuilder;

class PersonService
{

    private PersonDAO $personDAO;
    private SessionManager $sessionManager;


    public function __construct(PersonDAO $personDAO, SessionManager $sessionManager)
    {
        $this->personDAO = $personDAO;
        $this->sessionManager = $sessionManager;
    }


    public function getAllPeople(): array
    {
        return $this->personDAO->getAllPeople();
    }


    public function getPersonById(int $id): ?Person
    {
        return $this->personDAO->getPersonById($id);
    }


    public function getPersonByLogin(string $login): ?Person
    {
        return $this->personDAO->getPersonByLogin($login);
    }


    public function updatePerson(array $parameters): void
    {
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


    public function getPersonByIdentity(Identity $identity): ?Person
    {
        return $this->personDAO->getPerson($identity);
    }


    public function createPerson(array $parameters): int
    {
        $person = PersonBuilder::aPerson()
            ->withIdentity(new Identity($parameters['first_name'], $parameters['last_name'], $parameters['picture']))
            ->withBiography($parameters['biography'])
            ->withDescription($parameters['description'])
            ->withColor($parameters['color'])
            ->withStartYear($parameters['start_year'] ?? 2022)
            ->build();

        return $this->personDAO->createPerson($person);
    }


    public function deletePerson(Person $person): void
    {
        $this->personDAO->deletePerson($person);
    }

}
