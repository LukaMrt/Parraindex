<?php

declare(strict_types=1);

namespace App\Tests\unit\application\sponsor;

use App\Application\person\PersonDAO;
use App\Application\sponsor\SponsorDAO;
use App\Application\sponsor\SponsorService;
use App\Entity\old\person\Identity;
use App\Entity\old\person\Person;
use App\Entity\old\person\PersonBuilder;
use App\Entity\old\sponsor\ClassicSponsor;
use App\Entity\old\sponsor\HeartSponsor;
use App\Entity\old\sponsor\Sponsor;
use PHPUnit\Framework\TestCase;

final class SponsorServiceTest extends TestCase
{
    private const string TEST_DATE = '2020-01-01';

    private Person $person;

    private Sponsor $sponsor;

    private SponsorService $sponsorService;

    private SponsorDAO $sponsorDAO;

    private PersonDAO $personDAO;


    #[\Override]
    protected function setUp(): void
    {
        $person               = PersonBuilder::aPerson()->withId(1)->build();
        $this->sponsor        = new ClassicSponsor(1, $person, $person, '', '');
        $this->person         = PersonBuilder::aPerson()
            ->withId(1)
            ->withIdentity(new Identity('test', 'test'))
            ->build();
        $this->sponsorDAO     = $this->createMock(SponsorDAO::class);
        $this->personDAO      = $this->createMock(PersonDAO::class);
        $this->sponsorService = new SponsorService($this->sponsorDAO, $this->personDAO);
    }


    public function testGetpersonfamilyRetrievesGodFathersAndGodSons(): void
    {

        $person  = PersonBuilder::aPerson()->withId(1)->withIdentity(new Identity('test', 'test'))->build();
        $person2 = PersonBuilder::aPerson()->withId(2)->withIdentity(new Identity('test2', 'test2'))->build();
        $person3 = PersonBuilder::aPerson()->withId(3)->withIdentity(new Identity('test3', 'test3'))->build();

        $godFather1 = new ClassicSponsor(1, $person2, $person, '', '');
        $godFather2 = new ClassicSponsor(2, $person3, $person, '', '');

        $godSon1 = new ClassicSponsor(3, $person, $person2, '', '');
        $godSon2 = new ClassicSponsor(4, $person, $person3, '', '');

        $this->sponsorDAO->method('getPersonFamily')
            ->with(1)
            ->willReturn([
                'person' => $person,
                'godFathers' => [$godFather1, $godFather2],
                'godChildren' => [$godSon1, $godSon2]
            ]);

        $family = $this->sponsorService->getPersonFamily(1);

        $this->assertEquals($family, [
            'person' => $person,
            'godFathers' => [$godFather1, $godFather2],
            'godChildren' => [$godSon1, $godSon2]
        ]);
    }


    public function testGetsponsorbyidReturnsNullWhenSponsorDoesNotExist(): void
    {

        $this->sponsorDAO->method('getSponsorById')->willReturn(null);

        $sponsor = $this->sponsorService->getSponsorById(1);

        $this->assertNull($sponsor);
    }


    public function testGetsponsorbyidReturnsSponsorWhenSponsorExists(): void
    {

        $this->sponsorDAO->method('getSponsorById')
            ->with(1)
            ->willReturn($this->sponsor);

        $this->personDAO->method('getPersonById')
            ->with(1)
            ->willReturn($this->person);

        $sponsor = $this->sponsorService->getSponsorById(1);

        $classicSponsor = new ClassicSponsor(1, $this->person, $this->person, '', '');
        $this->assertEquals($classicSponsor, $sponsor);
    }


    public function testAddsponsorCallsAddSponsorOnSponsorDAO(): void
    {

        $this->sponsorDAO->expects($this->once())
            ->method('addSponsor')
            ->with($this->sponsor);

        $this->sponsorService->addSponsor($this->sponsor);
    }


    public function testRemovesponsorCallsUpdateSponsorOnSponsorDAO(): void
    {

        $this->sponsorDAO->expects($this->once())
            ->method('removeSponsor')
            ->with(-1);

        $this->sponsorService->removeSponsor(-1);
    }


    public function testGestsponsorReturnsSponsor(): void
    {

        $this->sponsorDAO->method('getSponsorById')
            ->with(1)
            ->willReturn($this->sponsor);

        $sponsor = $this->sponsorService->getSponsor(1);

        $this->assertEquals($this->sponsor, $sponsor);
    }


    public function testCreatesponsorDoesNothingWhenSponsorAlreadyExists(): void
    {

        $this->personDAO->method('getPersonById')->with([1])->willReturn($this->person);

        $this->sponsorDAO->method('getSponsorByPeopleId')
            ->with(1, 1)
            ->willReturn($this->sponsor);

        $this->sponsorDAO->expects($this->never())
            ->method('addSponsor');

        $this->sponsorService->createSponsor([
            'godFatherId' => 1,
            'godChildId' => 1
        ]);
    }


    public function testCreatesponsorRegistersClassicSponsor(): void
    {

        $this->personDAO->method('getPersonById')->with([1])->willReturn($this->person);

        $this->sponsorDAO->method('getSponsorByPeopleId')
            ->with(1, 1)
            ->willReturn(null);

        $classicSponsor = new ClassicSponsor(-1, $this->person, $this->person, self::TEST_DATE, 'description');

        $this->sponsorDAO->expects($this->once())
            ->method('addSponsor')
            ->with($classicSponsor);

        $this->sponsorService->createSponsor([
            'godFatherId' => 1,
            'godChildId' => 1,
            'sponsorType' => '0',
            'sponsorDate' => self::TEST_DATE,
            'description' => 'description'
        ]);
    }


    public function testCreatesponsorRegistersHeartSponsor(): void
    {

        $this->personDAO->method('getPersonById')->with([1])->willReturn($this->person);

        $this->sponsorDAO->method('getSponsorByPeopleId')
            ->with(1, 1)
            ->willReturn(null);

        $heartSponsor = new HeartSponsor(-1, $this->person, $this->person, self::TEST_DATE, 'description');

        $this->sponsorDAO->expects($this->once())
            ->method('addSponsor')
            ->with($heartSponsor);

        $this->sponsorService->createSponsor([
            'godFatherId' => 1,
            'godChildId' => 1,
            'sponsorType' => '1',
            'sponsorDate' => self::TEST_DATE,
            'description' => 'description'
        ]);
    }


    public function testUpdatesponsorDoesNothingWhenSponsorDoesNotExist(): void
    {

        $this->sponsorDAO->method('getSponsorById')
            ->with(1)
            ->willReturn(null);

        $this->sponsorDAO->expects($this->never())
            ->method('updateSponsor');

        $this->sponsorService->updateSponsor(1, [
            'godFatherId' => 1,
            'godChildId' => 1
        ]);
    }


    public function testUpdatesponsorDoesNothingWhenTypeIsUnknown(): void
    {

        $this->sponsorDAO->method('getSponsorById')
            ->with(1)
            ->willReturn($this->sponsor);

        $this->sponsorDAO->expects($this->never())
            ->method('updateSponsor');

        $this->sponsorService->updateSponsor(1, [
            'godFatherId' => 1,
            'godChildId' => 1,
            'sponsorType' => '2'
        ]);
    }


    public function testUpdatesponsorRegistersClassicSponsor(): void
    {

        $this->sponsorDAO->method('getSponsorById')
            ->with(1)
            ->willReturn($this->sponsor);

        $person  = PersonBuilder::aPerson()->withId(1)->build();
        $classicSponsor = new ClassicSponsor(1, $person, $person, self::TEST_DATE, 'description');

        $this->sponsorDAO->expects($this->once())
            ->method('updateSponsor')
            ->with($classicSponsor);

        $this->sponsorService->updateSponsor(1, [
            'sponsorType' => '0',
            'sponsorDate' => self::TEST_DATE,
            'description' => 'description'
        ]);
    }


    public function testUpdatesponsorRegistersHeartSponsor(): void
    {

        $this->sponsorDAO->method('getSponsorById')
            ->with(1)
            ->willReturn($this->sponsor);

        $person  = PersonBuilder::aPerson()->withId(1)->build();
        $heartSponsor = new HeartSponsor(1, $person, $person, self::TEST_DATE, 'description');

        $this->sponsorDAO->expects($this->once())
            ->method('updateSponsor')
            ->with($heartSponsor);

        $this->sponsorService->updateSponsor(1, [
            'sponsorType' => '1',
            'sponsorDate' => self::TEST_DATE,
            'description' => 'description'
        ]);
    }
}
