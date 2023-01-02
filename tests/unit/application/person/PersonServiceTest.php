<?php

namespace unit\application\person;

use App\application\login\SessionManager;
use App\application\person\PersonDAO;
use App\application\person\PersonService;
use App\model\person\Identity;
use App\model\person\Person;
use App\model\person\PersonBuilder;
use PHPUnit\Framework\TestCase;

class PersonServiceTest extends TestCase {

	private Person $person;

    private PersonService $personService;
	private SessionManager $sessionManager;
    private PersonDAO $personDAO;

    public function setUp(): void {
		$this->person = PersonBuilder::aPerson()
            ->withId(1)
			->withIdentity(new Identity('test', 'test', 'test'))
			->withBiography('test')
			->withDescription('test')
			->withColor('test')
			->build();

        $this->personDAO = $this->createMock(PersonDAO::class);
		$this->sessionManager = $this->createMock(SessionManager::class);

        $this->personService = new PersonService($this->personDAO, $this->sessionManager);
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

		$updatedPerson = PersonBuilder::aPerson()
			->withId(1)
			->withIdentity(new Identity('newFirstName', 'newLastName', 'newPicture'))
			->withBiography('newBio')
			->withDescription('NewDesc')
			->withColor('newColor')
			->build();

        $this->sessionManager->method('get')
            ->with('user')
            ->willReturn($this->person);

		$this->personDAO->expects($this->once())
			->method('updatePerson')
			->with($updatedPerson);

		$this->personService->updatePerson(array(
			'id' => 1,
			'first_name' => 'newFirstName',
			'last_name' => 'newLastName',
			'picture' => 'newPicture',
			'biography' => 'newBio',
			'description' => 'NewDesc',
			'color' => 'newColor'
		));
	}

	public function testGetpersonbyidentityReturnsPerson() {

		$this->personDAO->method('getPerson')
			->with($this->equalTo(new Identity('test', 'test')))
			->willReturn($this->person);

		$return = $this->personService->getPersonByIdentity(new Identity('test', 'test'));

		$this->assertEquals($return, $this->person);
	}

    public function testCreatepersonCreatesPerson() {
        $createPerson = PersonBuilder::aPerson()
            ->withIdentity(new Identity('newFirstName', 'newLastName', 'newPicture'))
            ->withBiography('newBio')
            ->withDescription('NewDesc')
            ->withColor('newColor')
            ->build();
	public function testAddpersonCallsPersonDAO() {

        $this->personDAO->expects($this->once())
            ->method('createPerson')
            ->with($createPerson);
		$this->personDAO->expects($this->once())
			->method('addPerson')
			->with($this->person);

        $this->personService->createPerson(array(
            'first_name' => 'newFirstName',
            'last_name' => 'newLastName',
            'picture' => 'newPicture',
            'biography' => 'newBio',
            'description' => 'NewDesc',
            'color' => 'newColor'
        ));
    }

    public function testCreatepersonReturnsIdOfTheCreatedPerson(){
        $createPerson = PersonBuilder::aPerson()
            ->withIdentity(new Identity('newFirstName', 'newLastName', 'newPicture'))
            ->withBiography('newBio')
            ->withDescription('NewDesc')
            ->withColor('newColor')
            ->build();

        $this->personDAO->method('createPerson')
            ->with($createPerson)
            ->willReturn(1);

        $return = $this->personService->createPerson(array(
            'first_name' => 'newFirstName',
            'last_name' => 'newLastName',
            'picture' => 'newPicture',
            'biography' => 'newBio',
            'description' => 'NewDesc',
            'color' => 'newColor'
        ));

        $this->assertEquals($return, 1);
    }

    public function testDeletepersonDeletesPerson() {
        $this->personDAO->expects($this->once())
            ->method('deletePerson')
            ->with($this->person);

        $this->personService->deletePerson($this->person);
    }
		$this->personService->addPerson($this->person);
	}

	public function testRemoveepersonCallsPersonDAO() {

		$this->personDAO->expects($this->once())
			->method('removePerson')
			->with(-1);

		$this->personService->removePerson(-1);
	}

}