<?php

namespace App\Application\person;

use App\Entity\person\Identity;
use App\Entity\person\Person;

/**
 * Person data access object
 */
interface PersonDAO
{
    /**
     * Get all persons
     * @return array
     */
    public function getAllPeople(): array;


    /**
     * Get person by identity
     * @param Identity $identity Identity
     * @return Person|null
     */
    public function getPerson(Identity $identity): ?Person;


    /**
     * Get person by id
     * @param int $id Id
     * @return Person|null
     */
    public function getPersonById(int $id): ?Person;


    /**
     * Update person
     * @param Person $person Person
     * @return void
     */
    public function updatePerson(Person $person): void;


    /**
     * Create person
     * @param Person $person Person
     * @return int
     */
    public function createPerson(Person $person): int;


    /**
     * Delete person
     * @param Person $person Person
     * @return void
     */
    public function deletePerson(Person $person): void;


    /**
     * Get all identities
     * @return array
     */
    public function getAllIdentities(): array;


    /**
     * Get person by login
     * @param string $login Login
     * @return Person|null
     */
    public function getPersonByLogin(string $login): ?Person;
}
