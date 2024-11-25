<?php

declare(strict_types=1);

namespace App\Tests\unit\application\contact\executor;

use App\Application\contact\ContactDAO;
use App\Application\contact\executor\AddSponsorContactExecutor;
use App\Application\person\PersonDAO;
use App\Application\redirect\Redirect;
use App\Application\sponsor\SponsorDAO;
use App\Entity\Contact\Type;
use App\Entity\old\contact\SponsorContact;
use App\Entity\old\person\Person;
use App\Entity\old\sponsor\ClassicSponsor;
use App\Entity\old\sponsor\HeartSponsor;
use App\Entity\old\sponsor\Sponsor;
use PHPUnit\Framework\TestCase;

final class AddSponsorContactExecutorTest extends TestCase
{
    private const string TEST_EMAIL = 'test.test@test.com';

    private const string TEST_DATE = '2021-01-01';

    private AddSponsorContactExecutor $addSponsorContactExecutor;

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


    #[\Override]
    protected function setUp(): void
    {
        $this->contactDAO = $this->createMock(ContactDAO::class);
        $redirect         = $this->createMock(Redirect::class);
        $this->personDAO  = $this->createMock(PersonDAO::class);
        $this->sponsorDAO = $this->createMock(SponsorDAO::class);

        $this->addSponsorContactExecutor = new AddSponsorContactExecutor(
            $this->contactDAO,
            $redirect,
            $this->personDAO,
            $this->sponsorDAO
        );
    }


    public function testExecuteReturnsErrorWhenSenderFirstNameIsMissing(): void
    {
        $this->defaultArray['senderFirstName'] = '';

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturn($this->createMock(Person::class));

        $result = $this->addSponsorContactExecutor->execute($this->defaultArray);

        $this->assertSame('Votre prénom doit contenir au moins 1 caractère', $result);
    }


    public function testExecuteReturnsErrorWhenGodfatherDoesNotExist(): void
    {

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturnOnConsecutiveCalls(null, $this->createMock(Person::class));

        $result = $this->addSponsorContactExecutor->execute($this->defaultArray);

        $this->assertSame('Le parrain doit exister', $result);
    }


    public function testExecuteReturnsErrorWhenGodchildDoesNotExist(): void
    {

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturnOnConsecutiveCalls($this->createMock(Person::class), null);

        $result = $this->addSponsorContactExecutor->execute($this->defaultArray);

        $this->assertSame('Le fillot doit exister', $result);
    }


    public function testExecuteReturnsErrorWhenSponsorTypeIsMinus1(): void
    {
        $this->defaultArray['sponsorType'] = '-1';

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturn($this->createMock(Person::class));

        $result = $this->addSponsorContactExecutor->execute($this->defaultArray);

        $this->assertSame('Le type de lien doit être valide', $result);
    }


    public function testExecuteReturnsErrorWhenSponsorTypeIs2(): void
    {
        $this->defaultArray['sponsorType'] = '2';

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturn($this->createMock(Person::class));

        $result = $this->addSponsorContactExecutor->execute($this->defaultArray);

        $this->assertSame('Le type de lien doit être valide', $result);
    }


    public function testExecuteReturnsNothingWhenSponsorTypeIs1(): void
    {
        $this->defaultArray['sponsorType'] = '1';

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturn($this->createMock(Person::class));

        $result = $this->addSponsorContactExecutor->execute($this->defaultArray);

        $this->assertSame('', $result);
    }


    public function testExecutesuccessReturnsErrorWhenSponsorAlreadyExists(): void
    {

        $this->sponsorDAO->method('getSponsorByPeopleId')
            ->with(1, 2)
            ->willReturn($this->createMock(Sponsor::class));

        $result = $this->addSponsorContactExecutor->executeSuccess($this->defaultArray);

        $this->assertSame('Le lien ne doit pas déjà exister', $result);
    }


    public function testExecutesuccessSavesClassicSponsorWhenTypeIs0(): void
    {

        $godFather = $this->createMock(Person::class);
        $godChild  = $this->createMock(Person::class);

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturnOnConsecutiveCalls($godFather, $godChild);

        $classicSponsor = new ClassicSponsor(-1, $godFather, $godChild, self::TEST_DATE, '');

        $sponsorContact = new SponsorContact(
            -1,
            date('Y-m-d'),
            null,
            'test1 test2',
            self::TEST_EMAIL,
            Type::ADD_SPONSOR,
            'empty',
            $classicSponsor
        );

        $this->contactDAO->expects($this->once())
            ->method('saveSponsorContact')
            ->with($sponsorContact);

        $this->addSponsorContactExecutor->executeSuccess($this->defaultArray);
    }


    public function testExecutesuccessSavesHeartSponsorWhenTypeIs1(): void
    {

        $godFather = $this->createMock(Person::class);
        $godChild  = $this->createMock(Person::class);

        $this->personDAO->method('getPersonById')
            ->withConsecutive([1], [2])
            ->willReturnOnConsecutiveCalls($godFather, $godChild);

        $heartSponsor = new HeartSponsor(-1, $godFather, $godChild, self::TEST_DATE, '');

        $sponsorContact = new SponsorContact(
            -1,
            date('Y-m-d'),
            null,
            'test1 test2',
            self::TEST_EMAIL,
            Type::ADD_SPONSOR,
            'empty',
            $heartSponsor
        );

        $this->contactDAO->expects($this->once())
            ->method('saveSponsorContact')
            ->with($sponsorContact);

        $this->defaultArray['sponsorType'] = '1';

        $this->addSponsorContactExecutor->executeSuccess($this->defaultArray);
    }
}
