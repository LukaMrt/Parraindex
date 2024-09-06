<?php

namespace App\Application\person;

use App\Application\logging\Logger;
use App\Application\login\SessionManager;
use App\Application\sponsor\SponsorDAO;
use App\Entity\person\Identity;
use App\Entity\person\Person;
use App\Entity\person\PersonBuilder;

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
     * @var SponsorDAO Sponsor data access object
     */
    private SponsorDAO $sponsorDAO;

    /**
     * @var SessionManager Session manager
     */
    private SessionManager $sessionManager;

    /**
     * @var Logger Logger
     */
    private Logger $logger;


    /**
     * @param PersonDAO $personDAO Person data access object
     * @param SessionManager $sessionManager Session manager
     * @param Logger $logger Logger
     */
    public function __construct(
        PersonDAO $personDAO,
        SessionManager $sessionManager,
        Logger $logger,
        SponsorDAO $sponsorDAO
    ) {
        $this->personDAO = $personDAO;
        $this->sponsorDAO = $sponsorDAO;
        $this->sessionManager = $sessionManager;
        $this->logger = $logger;
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

        $this->logger->info(
            PersonService::class,
            'Person ' . $parameters['first_name'] . ' ' . $parameters['last_name'] . ' updated.'
        );

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

        $this->logger->info(
            PersonService::class,
            'Person ' . $parameters['first_name'] . ' ' . $parameters['last_name'] . ' created.'
        );

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


    /**
     * Retrieves all data from a person
     * @param int $personId Id of the person
     * @return ?Person Person with full data
     */
    public function getPersonData(int $personId): ?Person
    {
        $person = $this->personDAO->getPersonById($personId);

        if ($person === null) {
            return null;
        }

        $data = $this->sponsorDAO->getPersonFamily($personId);
        $person->setCharacteristics($data["person"]->getCharacteristics());

        foreach ($data["godFathers"] as $sponsor) {
            $sponsor->setGodChild(null, true);
            $sponsor->getGodFather()->setCharacteristics([]);
            $person->addSponsors([$sponsor]);
        }

        foreach ($data["godChildren"] as $sponsor) {
            $sponsor->setGodFather(null, true);
            $sponsor->getGodChild()->setCharacteristics([]);
            $person->addSponsors([$sponsor]);
        }

        return $person;
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
}
