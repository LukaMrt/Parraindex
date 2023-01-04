<?php

namespace App\application\person;

use App\application\login\SessionManager;
use App\model\person\Identity;
use App\model\person\Person;
use App\model\person\PersonBuilder;

/**
 * Do person relate actions
 */
class PersonService
{
    /**
     * @var PersonDAO Person data access object
     */
    private PersonDAO $personDAO;
    /**
     * @var SessionManager Session manager
     */
    private SessionManager $sessionManager;


    /**
     * @param PersonDAO $personDAO Person data access object
     * @param SessionManager $sessionManager Session manager
     */
    public function __construct(PersonDAO $personDAO, SessionManager $sessionManager)
    {
        $this->personDAO = $personDAO;
        $this->sessionManager = $sessionManager;
    }


    /**
     * Get all persons
     * @return array
     */
    public function getAllPeople(): array
    {
        return $this->personDAO->getAllPeople();
    }


    /**
     * Get person by id
     * @param int $id Id
     * @return Person|null
     */
    public function getPersonById(int $id): ?Person
    {
        return $this->personDAO->getPersonById($id);
    }


    /**
     * Get person by login
     * @param string $login Login
     * @return Person|null
     */
    public function getPersonByLogin(string $login): ?Person
    {
        return $this->personDAO->getPersonByLogin($login);
    }


    /**
     * Update person
     * @param array $parameters Parameters
     * @return void
     */
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


    /**
     * Get person by identity
     * @param Identity $identity Identity
     * @return Person|null
     */
    public function getPersonByIdentity(Identity $identity): ?Person
    {
        return $this->personDAO->getPerson($identity);
    }


    /**
     * Create person
     * @param array $parameters Parameters
     * @return int
     */
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


    /**
     * Delete person
     * @param Person $person Person
     * @return void
     */
    public function deletePerson(Person $person): void
    {
        $this->personDAO->deletePerson($person);
    }
}
