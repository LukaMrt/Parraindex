<?php

namespace unit\application\sponsor;

use App\application\person\PersonDAO;
use App\application\sponsor\SponsorDAO;
use App\application\sponsor\SponsorService;
use App\model\person\Identity;
use App\model\person\Person;
use App\model\person\PersonBuilder;
use App\model\sponsor\ClassicSponsor;
use App\model\sponsor\Sponsor;
use DateTime;
use PHPUnit\Framework\TestCase;

class SponsorServiceTest extends TestCase {

	private Person $person;
	private Sponsor $sponsor;
    private SponsorService $sponsorService;
    private SponsorDAO $sponsorDAO;
	private PersonDAO $personDAO;

    public function setUp(): void {
		$person = PersonBuilder::aPerson()->withId(1)->build();
		$this->person = PersonBuilder::aPerson()->withId(1)->withIdentity(new Identity('test', 'test'))->build();
		$this->sponsor = new ClassicSponsor(1, $person, $person, '', '');
        $this->sponsorDAO = $this->createMock(SponsorDAO::class);
		$this->personDAO = $this->createMock(PersonDAO::class);
        $this->sponsorService = new SponsorService($this->sponsorDAO, $this->personDAO);
    }

    public function testGetpersonfamilyRetrievesGodFathersAndGodSons() {

        $person = $this->createMock(Person::class);
        $godFather1 = $this->createMock(Person::class);
        $godFather2 = $this->createMock(Person::class);
        $godSon1 = $this->createMock(Person::class);
        $godSon2 = $this->createMock(Person::class);

        $this->sponsorDAO->method('getPersonFamily')
            ->with($this->equalTo(1))
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

	public function testGetsponsorbyidReturnsNullWhenSponsorDoesNotExist(): void {

		$this->sponsorDAO->method('getSponsorById')->willReturn(null);

		$sponsor = $this->sponsorService->getSponsorById(1);

		$this->assertNull($sponsor);
	}

	public function testGetsponsorbyidReturnsSponsorWhenSponsorExists(): void {

		$this->sponsorDAO->method('getSponsorById')
			->with(1)
			->willReturn($this->sponsor);

		$this->personDAO->method('getPersonById')
			->with(1)
			->willReturn($this->person);

		$sponsor = $this->sponsorService->getSponsorById(1);

		$this->assertEquals([
			'sponsor' => $this->sponsor,
			'godFather' => $this->person,
			'godChild' => $this->person
		], $sponsor);
	}

}