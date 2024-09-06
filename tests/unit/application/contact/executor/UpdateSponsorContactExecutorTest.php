<?php

namespace App\Tests\unit\application\contact\executor;

use App\Application\contact\ContactDAO;
use App\Application\contact\executor\UpdateSponsorContactExecutor;
use App\Application\person\PersonDAO;
use App\Application\redirect\Redirect;
use App\Application\sponsor\SponsorDAO;
use App\Entity\contact\ContactType;
use App\Entity\contact\SponsorContact;
use App\Entity\person\Person;
use App\Entity\sponsor\Sponsor;
use PHPUnit\Framework\TestCase;

class UpdateSponsorContactExecutorTest extends TestCase
{
    private UpdateSponsorContactExecutor $executor;

    private ContactDAO $contactDAO;
    private Redirect $redirect;
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


    public function setUp(): void
    {
        $this->contactDAO = $this->createMock(ContactDAO::class);
        $this->redirect = $this->createMock(Redirect::class);
        $this->personDAO = $this->createMock(PersonDAO::class);
        $this->sponsorDAO = $this->createMock(SponsorDAO::class);

        $this->executor = new UpdateSponsorContactExecutor(
            $this->contactDAO,
            $this->personDAO,
            $this->sponsorDAO,
            $this->redirect
        );
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


    public function testExecutesuccessReturnsErrorWhenSponsorAlreadyExists(): void
    {

        $this->sponsorDAO->method('getSponsorByPeopleId')
            ->with(1, 2)
            ->willReturn(null);

        $result = $this->executor->executeSuccess($this->defaultArray);

        $this->assertEquals('Le lien doit exister', $result);
    }


    public function testExecutesuccessSavesClassicSponsorWhenTypeIs0(): void
    {

        $sponsor = $this->createMock(Sponsor::class);

        $this->sponsorDAO->method('getSponsorByPeopleId')
            ->with(1, 2)
            ->willReturn($sponsor);

        $contact = new SponsorContact(
            -1,
            date('Y-m-d'),
            null,
            'test1 test2',
            'test.test@test.com',
            ContactType::UPDATE_SPONSOR,
            'empty',
            $sponsor
        );

        $this->contactDAO->expects($this->once())
            ->method('saveSponsorContact')
            ->with($contact);

        $this->executor->executeSuccess($this->defaultArray);
    }
}
