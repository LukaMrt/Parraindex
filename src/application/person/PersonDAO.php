<?php

namespace App\application\person;

use App\model\person\Identity;
use App\model\person\Person;

interface PersonDAO {

	public function getAllPeople(): array;

	public function getPerson(Identity $identity): ?Person;

	public function getPersonById(int $id): ?Person;

	public function updatePerson(Person $person);

	public function createPerson(Person $person): int;

	public function deletePerson(Person $person) : bool;

	public function getAllIdentities(): array;

	public function getPersonByLogin(string $login): ?Person;

	public function addPerson(Person $person): void;

	public function removePerson(int $id);

}