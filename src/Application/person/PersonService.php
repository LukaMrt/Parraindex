<?php

namespace App\Application\person;

use App\Application\logging\Logger;
use App\Entity\Person\Person;
use App\Repository\PersonRepository;

readonly class PersonService
{
    public function __construct(
        private PersonRepository $personRepository,
        private Logger           $logger,
    ) {
    }

    /**
     * @return Person[]
     */
    public function getAllPeople(): array
    {
        return $this->personRepository->getAll();
    }

    public function getPersonByLogin(string $login): ?Person
    {
        return $this->personRepository->getByEmail($login);
    }

    public function getPersonByIdentity(string $firstName, string $lastName): ?Person
    {
        return $this->personRepository->getByIdentity($firstName, $lastName);
    }

    public function deletePerson(Person $person): void
    {
        $this->logger->info(
            PersonService::class,
            'Person ' . $person->getFirstName() . ' ' . $person->getLastName() . ' deleted.'
        );

        $this->personRepository->delete($person);
    }

    public function getPersonById(int $id): ?Person
    {
        return $this->personRepository->getById($id);
    }

    public function updatePerson(array $parameters): void
    {
        $person = $this->personRepository->getById($parameters['id']);

        if ($person === null) {
            return;
        }

        $person->setFirstName($parameters['first_name'])
            ->setLastName($parameters['last_name'])
            ->setPicture($parameters['picture'])
            ->setBiography($parameters['biography'])
            ->setDescription($parameters['description'])
            ->setColor($parameters['color'])
            ->setStartYear($parameters['start_year']);

        $this->logger->info(
            self::class,
            'Person ' . $person->getFirstName() . ' ' . $person->getLastName() . ' updated.'
        );

        $this->personRepository->update($person);
    }

    public function createPerson(array $parameters): void
    {
        $person = (new Person())
            ->setFirstName($parameters['first_name'])
            ->setLastName($parameters['last_name'])
            ->setPicture($parameters['picture'])
            ->setBiography($parameters['biography'])
            ->setDescription($parameters['description'])
            ->setColor($parameters['color'])
            ->setStartYear($parameters['start_year'])
            ->setCreatedAt(new \DateTimeImmutable());

        $this->logger->info(
            self::class,
            'Person ' . $person->getFirstName() . ' ' . $person->getLastName() . ' created.'
        );

        $this->personRepository->update($person);
    }
}
