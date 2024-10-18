<?php

declare(strict_types=1);

namespace App\Application\person;

use App\Entity\old\person\Identity;
use App\Entity\old\person\Person;

/**
 * Person data access object
 */
interface PersonDAO
{
    /**
     * Get all persons
     */
    public function getAllPeople(): array;


    /**
     * Get person by identity
     * @param Identity $identity Identity
     */
    public function getPerson(Identity $identity): ?Person;


    /**
     * Get person by id
     * @param int $id Id
     */
    public function getPersonById(int $id): ?Person;


    /**
     * Update person
     * @param Person $person Person
     */
    public function updatePerson(Person $person): void;


    /**
     * Create person
     * @param Person $person Person
     */
    public function createPerson(Person $person): int;


    /**
     * Delete person
     * @param Person $person Person
     */
    public function deletePerson(Person $person): void;


    /**
     * Get all identities
     */
    public function getAllIdentities(): array;


    /**
     * Get person by login
     * @param string $login Login
     */
    public function getPersonByLogin(string $login): ?Person;
}
