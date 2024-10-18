<?php

declare(strict_types=1);

namespace App\Tests\unit\application\contact\executor;

use App\Application\contact\ContactDAO;
use App\Application\contact\executor\RemovePersonContactExecutor;
use App\Application\person\PersonDAO;
use App\Application\redirect\Redirect;
use App\Entity\Contact\Type;
use App\Entity\old\contact\PersonContact;
use App\Entity\old\person\Identity;
use App\Entity\old\person\Person;
use App\Entity\old\person\PersonBuilder;
use PHPUnit\Framework\TestCase;

final class RemovePersonContactExecutorTest extends TestCase
{
    private RemovePersonContactExecutor $removePersonContactExecutor;

    private ContactDAO $contactDAO;

    private PersonDAO $personDAO;

    private array $defaultArray = [
        'senderFirstName' => 'test1',
        'senderLastName' => 'test2',
        'senderEmail' => 'test.test@test.com',
        'personId' => 1,
        'message' => 'empty'
    ];


    #[\Override]
    protected function setUp(): void
    {

        $this->contactDAO = $this->createMock(ContactDAO::class);
        $redirect         = $this->createMock(Redirect::class);
        $this->personDAO  = $this->createMock(PersonDAO::class);

        $this->removePersonContactExecutor = new RemovePersonContactExecutor($this->personDAO, $this->contactDAO, $redirect);
    }


    public function testExecuteReturnsErrorWhenSenderFirstnameIsMissing(): void
    {

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturn($this->createMock(Person::class));

        $this->defaultArray['senderFirstName'] = '';

        $result = $this->removePersonContactExecutor->execute($this->defaultArray);

        $this->assertSame('Votre prénom doit contenir au moins 1 caractère', $result);
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

        $personContact = new PersonContact(
            -1,
            date('Y-m-d'),
            null,
            'test1 test2',
            'test.test@test.com',
            Type::REMOVE_PERSON,
            'empty',
            $person
        );

        $this->contactDAO->expects($this->once())
            ->method('savePersonRemoveContact')
            ->with($personContact);

        $this->removePersonContactExecutor->execute($this->defaultArray);
    }
}
