<?php

namespace App\Tests\unit\application\contact\executor;

use App\Application\contact\ContactDAO;
use App\Application\contact\executor\UpdatePersonContactExecutor;
use App\Application\person\PersonDAO;
use App\Application\redirect\Redirect;
use App\Entity\ContactType;
use App\Entity\old\contact\PersonContact;
use App\Entity\old\person\Identity;
use App\Entity\old\person\Person;
use App\Entity\old\person\PersonBuilder;
use PHPUnit\Framework\TestCase;

class UpdatePersonContactExecutorTest extends TestCase
{
    private UpdatePersonContactExecutor $executor;

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

        $this->executor = new UpdatePersonContactExecutor($this->personDAO, $this->contactDAO, $redirect);
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
            date('Y-m-d'),
            null,
            'test1 test2',
            'test.test@test.com',
            ContactType::UPDATE_PERSON,
            'empty',
            $person
        );

        $this->contactDAO->expects($this->once())
            ->method('savePersonUpdateContact')
            ->with($contact);

        $this->executor->execute($this->defaultArray);
    }
}
