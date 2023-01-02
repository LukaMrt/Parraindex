<?php

namespace unit\application\contact\executor;

use App\application\contact\ContactDAO;
use App\application\contact\executor\AddSponsorContactExecutor;
use App\application\person\PersonDAO;
use App\application\redirect\Redirect;
use App\application\sponsor\SponsorDAO;
use App\model\contact\ContactType;
use App\model\contact\SponsorContact;
use App\model\person\Person;
use App\model\sponsor\ClassicSponsor;
use App\model\sponsor\HeartSponsor;
use App\model\sponsor\Sponsor;
use PHPUnit\Framework\TestCase;

class AddSponsorContactExecutorTest extends TestCase
{

    const TEST_EMAIL = 'test.test@test.com';
    const TEST_DATE = '2021-01-01';
    private AddSponsorContactExecutor $executor;

    private ContactDAO $contactDAO;
    private PersonDAO $personDAO;
    private SponsorDAO $sponsorDAO;

    private array $defaultArray = [
        'senderFirstName' => 'test1',
        'senderLastName' => 'test2',
        'senderEmail' => self::TEST_EMAIL,
        'godFatherId' => 1,
        'godChildId' => 2,
        'sponsorType' => '0',
        'sponsorDate' => self::TEST_DATE,
        'bonusInformation' => 'empty'
    ];

    public function setUp(): void
    {
        $this->contactDAO = $this->createMock(ContactDAO::class);
        $redirect = $this->createMock(Redirect::class);
        $this->personDAO = $this->createMock(PersonDAO::class);
        $this->sponsorDAO = $this->createMock(SponsorDAO::class);

        $this->executor = new AddSponsorContactExecutor(
            $this->contactDAO,
            $redirect,
            $this->personDAO,
            $this->sponsorDAO
        );
    }

    public function testExecuteReturnsErrorWhenSenderFirstNameIsMissing()
    {
        $this->defaultArray['senderFirstName'] = '';

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturn($this->createMock(Person::class));

        $result = $this->executor->execute($this->defaultArray);

        $this->assertEquals('Votre prénom doit contenir au moins 1 caractère', $result);
    }

    public function testExecuteReturnsErrorWhenGodfatherDoesNotExist()
    {

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturnOnConsecutiveCalls(null, $this->createMock(Person::class));

        $result = $this->executor->execute($this->defaultArray);

        $this->assertEquals('Le parrain doit exister', $result);
    }

    public function testExecuteReturnsErrorWhenGodchildDoesNotExist()
    {

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturnOnConsecutiveCalls($this->createMock(Person::class), null);

        $result = $this->executor->execute($this->defaultArray);

        $this->assertEquals('Le fillot doit exister', $result);
    }

    public function testExecuteReturnsErrorWhenSponsorTypeIsMinus1(): void
    {
        $this->defaultArray['sponsorType'] = '-1';

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturn($this->createMock(Person::class));

        $result = $this->executor->execute($this->defaultArray);

        $this->assertEquals('Le type de lien doit être valide', $result);
    }

    public function testExecuteReturnsErrorWhenSponsorTypeIs2(): void
    {
        $this->defaultArray['sponsorType'] = '2';

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturn($this->createMock(Person::class));

        $result = $this->executor->execute($this->defaultArray);

        $this->assertEquals('Le type de lien doit être valide', $result);
    }

    public function testExecuteReturnsNothingWhenSponsorTypeIs1(): void
    {
        $this->defaultArray['sponsorType'] = '1';

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturn($this->createMock(Person::class));

        $result = $this->executor->execute($this->defaultArray);

        $this->assertEquals('', $result);
    }

    public function testExecutesuccessReturnsErrorWhenSponsorAlreadyExists(): void
    {

        $this->sponsorDAO->method('getSponsorByPeopleId')
            ->with(1, 2)
            ->willReturn($this->createMock(Sponsor::class));

        $result = $this->executor->executeSuccess($this->defaultArray);

        $this->assertEquals('Le lien ne doit pas déjà exister', $result);
    }

    public function testExecutesuccessSavesClassicSponsorWhenTypeIs0(): void
    {

        $godFather = $this->createMock(Person::class);
        $godChild = $this->createMock(Person::class);

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturnOnConsecutiveCalls($godFather, $godChild);

        $sponsor = new ClassicSponsor(-1, $godFather, $godChild, self::TEST_DATE, '');

        $contact = new SponsorContact(
            -1,
            'test1 test2',
            self::TEST_EMAIL,
            ContactType::ADD_SPONSOR,
            'empty',
            $sponsor
        );

        $this->contactDAO->expects($this->once())
            ->method('saveSponsorContact')
            ->with($contact);

        $this->executor->executeSuccess($this->defaultArray);
    }

    public function testExecutesuccessSavesHeartSponsorWhenTypeIs1(): void
    {

        $godFather = $this->createMock(Person::class);
        $godChild = $this->createMock(Person::class);

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturnOnConsecutiveCalls($godFather, $godChild);

        $sponsor = new HeartSponsor(-1, $godFather, $godChild, self::TEST_DATE, '');

        $contact = new SponsorContact(
            -1,
            'test1 test2',
            self::TEST_EMAIL,
            ContactType::ADD_SPONSOR,
            'empty',
            $sponsor
        );

        $this->contactDAO->expects($this->once())
            ->method('saveSponsorContact')
            ->with($contact);

        $this->defaultArray['sponsorType'] = '1';

        $this->executor->executeSuccess($this->defaultArray);
    }

}