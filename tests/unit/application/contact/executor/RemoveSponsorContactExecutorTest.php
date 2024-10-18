<?php

declare(strict_types=1);

namespace App\Tests\unit\application\contact\executor;

use App\Application\contact\ContactDAO;
use App\Application\contact\executor\RemoveSponsorContactExecutor;
use App\Application\person\PersonDAO;
use App\Application\redirect\Redirect;
use App\Application\sponsor\SponsorDAO;
use App\Entity\Contact\Type;
use App\Entity\old\contact\SponsorContact;
use App\Entity\old\person\Person;
use App\Entity\old\sponsor\Sponsor;
use PHPUnit\Framework\TestCase;

final class RemoveSponsorContactExecutorTest extends TestCase
{
    private RemoveSponsorContactExecutor $removeSponsorContactExecutor;

    private ContactDAO $contactDAO;

    private PersonDAO $personDAO;

    private SponsorDAO $sponsorDAO;

    private array $defaultArray = [
        'senderFirstName' => 'test1',
        'senderLastName' => 'test2',
        'senderEmail' => 'test.test@test.com',
        'godFatherId' => 1,
        'godChildId' => 2,
        'sponsorType' => '0',
        'sponsorDate' => '2021-01-01',
        'message' => 'empty'
    ];


    #[\Override]
    protected function setUp(): void
    {
        $this->contactDAO = $this->createMock(ContactDAO::class);
        $redirect         = $this->createMock(Redirect::class);
        $this->personDAO  = $this->createMock(PersonDAO::class);
        $this->sponsorDAO = $this->createMock(SponsorDAO::class);

        $this->removeSponsorContactExecutor = new RemoveSponsorContactExecutor(
            $this->contactDAO,
            $this->personDAO,
            $this->sponsorDAO,
            $redirect
        );
    }


    public function testExecuteReturnsErrorWhenSenderFirstnameIsMissing(): void
    {

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturn($this->createMock(Person::class));

        $this->defaultArray['senderFirstName'] = '';

        $result = $this->removeSponsorContactExecutor->execute($this->defaultArray);

        $this->assertSame('Votre prénom doit contenir au moins 1 caractère', $result);
    }


    public function testExecutesuccessReturnsErrorWhenSponsorAlreadyExists(): void
    {

        $this->sponsorDAO->method('getSponsorByPeopleId')
            ->with(1, 2)
            ->willReturn(null);

        $result = $this->removeSponsorContactExecutor->executeSuccess($this->defaultArray);

        $this->assertSame('Le lien doit exister', $result);
    }


    public function testExecutesuccessSavesClassicSponsorWhenTypeIs0(): void
    {

        $sponsor = $this->createMock(Sponsor::class);

        $this->sponsorDAO->method('getSponsorByPeopleId')
            ->with(1, 2)
            ->willReturn($sponsor);

        $sponsorContact = new SponsorContact(
            -1,
            date('Y-m-d'),
            null,
            'test1 test2',
            'test.test@test.com',
            Type::REMOVE_SPONSOR,
            'empty',
            $sponsor
        );

        $this->contactDAO->expects($this->once())
            ->method('saveSponsorContact')
            ->with($sponsorContact);

        $this->removeSponsorContactExecutor->executeSuccess($this->defaultArray);
    }
}
