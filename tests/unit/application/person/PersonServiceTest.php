<?php

namespace unit\application\person;

use App\application\person\PersonDAO;
use App\application\person\PersonService;
use App\model\person\Identity;
use App\model\person\Person;
use App\model\person\PersonBuilder;
use PHPUnit\Framework\TestCase;

class PersonServiceTest extends TestCase {

	private Person $person;

    private PersonService $personService;
    private PersonDAO $personDAO;

    public function setUp(): void {
		$this->person = PersonBuilder::aPerson()
			->withId(1)
			->withIdentity(new Identity('test', 'test'))
			->withBiography('test')
			->build();

        $this->personDAO = $this->createMock(PersonDAO::class);
        $this->personService = new PersonService($this->personDAO);
    }

    public function testGetallpeopleRetrievesPeopleList() {

		$this->personDAO->method('getAllPeople')
			->willReturn(array($this->person));

		$return = $this->personService->getAllPeople();

		$this->assertEquals($return, array($this->person));
	}

	public function testGetPersonByIdRetrievesPerson() {

		$person = $this->createMock(Person::class);
		$this->personDAO->method('getPersonById')
			->with(1)
			->willReturn($person);

		$return = $this->personService->getPersonById(1);

		$this->assertEquals($return, $person);
	}

	public function testGetPersonByLoginRetrievesPerson() {

		$person = $this->createMock(Person::class);
		$this->personDAO->method('getPersonByLogin')
			->with('test.test@etu.univ-lyon1.fr')
			->willReturn($person);

		$return = $this->personService->getPersonByLogin('test.test@etu.univ-lyon1.fr');

		$this->assertEquals($return, $person);
	}

	public function testUpdatepersonUpdatesDao() {

		$this->personDAO->expects($this->once())
			->method('updatePerson')
			->with($this->equalTo($this->person));

		$this->personService->updatePerson(array(
			'id' => 1,
			'first_name' => 'test',
			'last_name' => 'test',
			'biography' => 'test'
		));

	}

	public function testGetpersonbyidentityReturnsPerson() {

		$this->personDAO->method('getPerson')
			->with($this->equalTo(new Identity('test', 'test')))
			->willReturn($this->person);

		$return = $this->personService->getPersonByIdentity(new Identity('test', 'test'));

		$this->assertEquals($return, $this->person);
	}



}