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

}