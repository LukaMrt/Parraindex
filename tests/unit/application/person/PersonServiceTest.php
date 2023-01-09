<?php

namespace unit\application\person;

use App\application\logging\Logger;
use App\application\login\SessionManager;
use App\application\person\PersonDAO;
use App\application\person\PersonService;
use App\model\person\Identity;
use App\model\person\Person;
use App\model\person\PersonBuilder;
use PHPUnit\Framework\TestCase;

class PersonServiceTest extends TestCase
{

    private Person $person;

    private PersonService $personService;
    private SessionManager $sessionManager;
    private PersonDAO $personDAO;
    private Logger $logger;


    public function setUp(): void
    {
        $this->person = PersonBuilder::aPerson()
            ->withId(1)
            ->withIdentity(new Identity('test', 'test', 'test'))
            ->withBiography('test')
            ->withDescription('test')
            ->withColor('test')
            ->build();

        $this->personDAO = $this->createMock(PersonDAO::class);
        $this->sessionManager = $this->createMock(SessionManager::class);
        $this->logger = $this->createMock(Logger::class);

        $this->personService = new PersonService($this->personDAO, $this->sessionManager, $this->logger);
    }


    public function testGetallpeopleRetrievesPeopleList(): void
    {

        $this->personDAO->method('getAllPeople')
            ->willReturn([$this->person]);

        $return = $this->personService->getAllPeople();

        $this->assertEquals($return, [$this->person]);
    }


    public function testGetPersonByIdRetrievesPerson(): void
    {

        $person = $this->createMock(Person::class);
        $this->personDAO->method('getPersonById')
            ->with(1)
            ->willReturn($person);

        $return = $this->personService->getPersonById(1);

        $this->assertEquals($return, $person);
    }


    public function testGetPersonByLoginRetrievesPerson(): void
    {

        $person = $this->createMock(Person::class);
        $this->personDAO->method('getPersonByLogin')
            ->with('test.test@etu.univ-lyon1.fr')
            ->willReturn($person);

        $return = $this->personService->getPersonByLogin('test.test@etu.univ-lyon1.fr');

        $this->assertEquals($return, $person);
    }


    public function testUpdatePersonUpdatesSessionIfTheRequesterIsTheConnectedPerson(): void
    {

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

        $this->sessionManager->expects($this->once())
            ->method('set')
            ->with('user', $updatedPerson);

        $this->personService->updatePerson([
            'id' => 1,
            'first_name' => 'newFirstName',
            'last_name' => 'newLastName',
            'picture' => 'newPicture',
            'biography' => 'newBio',
            'description' => 'NewDesc',
            'color' => 'newColor'
        ]);

    }


    public function testUpdatepersonUpdatesDao(): void
    {

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

        $this->logger->expects($this->once())
            ->method('info')
            ->with(PersonService::class, 'Person newFirstName newLastName updated.');

        $this->personService->updatePerson([
            'id' => 1,
            'first_name' => 'newFirstName',
            'last_name' => 'newLastName',
            'picture' => 'newPicture',
            'biography' => 'newBio',
            'description' => 'NewDesc',
            'color' => 'newColor'
        ]);
    }


    public function testGetpersonbyidentityReturnsPerson(): void
    {

        $this->personDAO->method('getPerson')
            ->with($this->equalTo(new Identity('test', 'test')))
            ->willReturn($this->person);

        $return = $this->personService->getPersonByIdentity(new Identity('test', 'test'));

        $this->assertEquals($return, $this->person);
    }


    public function testCreatepersonReturnsIdOfTheCreatedPerson(): void
    {
        $createPerson = PersonBuilder::aPerson()
            ->withIdentity(new Identity('newFirstName', 'newLastName', 'newPicture'))
            ->withBiography('newBio')
            ->withDescription('NewDesc')
            ->withColor('newColor')
            ->withStartYear(2010)
            ->build();

        $this->personDAO->method('createPerson')
            ->with($createPerson)
            ->willReturn(1);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(PersonService::class, 'Person newFirstName newLastName created.');

        $return = $this->personService->createPerson([
            'first_name' => 'newFirstName',
            'last_name' => 'newLastName',
            'picture' => 'newPicture',
            'biography' => 'newBio',
            'description' => 'NewDesc',
            'color' => 'newColor',
            'start_year' => 2010
        ]);

        $this->assertEquals(1, $return);
    }


    public function testCreatepersonUses2022WhenNoStartYearIsProvided(): void
    {
        $createPerson = PersonBuilder::aPerson()
            ->withIdentity(new Identity('newFirstName', 'newLastName', 'newPicture'))
            ->withBiography('newBio')
            ->withDescription('NewDesc')
            ->withColor('newColor')
            ->withStartYear(2022)
            ->build();

        $this->personDAO->method('createPerson')
            ->with($createPerson)
            ->willReturn(1);

        $return = $this->personService->createPerson([
            'first_name' => 'newFirstName',
            'last_name' => 'newLastName',
            'picture' => 'newPicture',
            'biography' => 'newBio',
            'description' => 'NewDesc',
            'color' => 'newColor'
        ]);

        $this->assertEquals(1, $return);
    }


    public function testDeletepersonDeletesPerson(): void
    {
        $this->personDAO->expects($this->once())
            ->method('deletePerson')
            ->with($this->person);

        $this->personService->deletePerson($this->person);
    }

}