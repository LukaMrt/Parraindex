<?php

namespace unit\application\contact\executor;

use App\application\contact\ContactDAO;
use App\application\contact\executor\RemovePersonContactExecutor;
use App\application\person\PersonDAO;
use App\application\redirect\Redirect;
use App\model\contact\ContactType;
use App\model\contact\PersonContact;
use App\model\person\Identity;
use App\model\person\Person;
use App\model\person\PersonBuilder;
use PHPUnit\Framework\TestCase;

class RemovePersonContactExecutorTest extends TestCase
{

    private RemovePersonContactExecutor $executor;

    private ContactDAO $contactDAO;
    private PersonDAO $personDAO;

    private array $defaultArray = [
        'senderFirstName' => 'test1',
        'senderLastName' => 'test2',
        'senderEmail' => 'test.test@test.com',
        'personId' => 1,
        'message' => 'empty'
    ];

    public function setUp(): void
    {

        $this->contactDAO = $this->createMock(ContactDAO::class);
        $redirect = $this->createMock(Redirect::class);
        $this->personDAO = $this->createMock(PersonDAO::class);

        $this->executor = new RemovePersonContactExecutor($this->personDAO, $this->contactDAO, $redirect);
    }

    public function testExecuteReturnsErrorWhenSenderFirstnameIsMissing()
    {

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturn($this->createMock(Person::class));

        $this->defaultArray['senderFirstName'] = '';

        $result = $this->executor->execute($this->defaultArray);

        $this->assertEquals('Votre prénom doit contenir au moins 1 caractère', $result);
    }

    public function testExecuteSuccessSavesContactWithGivenValues(): void
    {

        $person = PersonBuilder::aPerson()
            ->withIdentity(new Identity('test3', 'test4'))
            ->withStartYear(2022)
            ->build();

        $this->personDAO->method('getPersonById')
            ->with(1)
            ->willReturn($person);

        $contact = new PersonContact(
            -1,
            'test1 test2',
            'test.test@test.com',
            ContactType::REMOVE_PERSON,
            'empty',
            $person
        );

        $this->contactDAO->expects($this->once())
            ->method('savePersonRemoveContact')
            ->with($contact);

        $this->executor->execute($this->defaultArray);
    }

}