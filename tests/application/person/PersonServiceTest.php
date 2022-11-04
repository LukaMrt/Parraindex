<?php

namespace person;

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

    public function testCanRetrieveEmptyList() {

        $this->personDAO->method('getAllPeople')
            ->willReturn(array());

        $allPeople = $this->personService->getAllPeople();

        $this->assertTrue($allPeople == array());
    }

    public function testCanRetrieveListWith1Element() {

        $person = $this->createMock(Person::class);
        $this->personDAO->method('getAllPeople')
            ->willReturn(array($person));

        $allPeople = $this->personService->getAllPeople();

        $this->assertTrue($allPeople == array($person));
    }

}