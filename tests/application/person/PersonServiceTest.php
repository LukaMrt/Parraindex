<?php

namespace application\person;

use App\application\person\PersonDAO;
use App\application\person\PersonService;
use App\model\person\Person;
use PHPUnit\Framework\TestCase;

class PersonServiceTest extends TestCase {

    private PersonService $personService;
    private PersonDAO $personDAO;

    public function setUp(): void {
        $this->personDAO = $this->createMock(PersonDAO::class);
        $this->personService = new PersonService($this->personDAO);
    }

    public function testGetallpeopleRetrievesPeopleList() {

		$person = $this->createMock(Person::class);
		$this->personDAO->method('getAllPeople')
			->will($this->onConsecutiveCalls(array(), array($person)));

		// Test 1
		$allPeople = $this->personService->getAllPeople();

		$this->assertTrue($allPeople == array());

		// Test 2
		$allPeople = $this->personService->getAllPeople();

		$this->assertTrue($allPeople == array($person));
	}

	public function testGetPersonByIdRetrievesPerson() {

		$person = $this->createMock(Person::class);
		$this->personDAO->method('getPersonById')
			->with($this->equalTo(1))
			->will($this->onConsecutiveCalls(null, $person));

		// Test 1
		$return = $this->personService->getPersonById(1);

		$this->assertTrue($return == null);

		// Test 2
		$return = $this->personService->getPersonById(1);

		$this->assertTrue($return === $person);
	}

}