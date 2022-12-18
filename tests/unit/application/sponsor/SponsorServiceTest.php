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
use PHPUnit\Framework\TestCase;

class SponsorServiceTest extends TestCase {

	private Person $person;
	private Sponsor $sponsor;
    private SponsorService $sponsorService;
    private SponsorDAO $sponsorDAO;
	private PersonDAO $personDAO;

    public function setUp(): void {
		$person = PersonBuilder::aPerson()->withId(1)->build();
		$this->sponsor = new ClassicSponsor(1, $person, $person, '', '');
		$this->person = PersonBuilder::aPerson()->withId(1)->withIdentity(new Identity('test', 'test'))->build();
        $this->sponsorDAO = $this->createMock(SponsorDAO::class);
		$this->personDAO = $this->createMock(PersonDAO::class);
        $this->sponsorService = new SponsorService($this->sponsorDAO, $this->personDAO);
    }

    public function testGetpersonfamilyRetrievesGodFathersAndGodSons() {

		$person = PersonBuilder::aPerson()->withId(1)->withIdentity(new Identity('test', 'test'))->build();
		$person2 = PersonBuilder::aPerson()->withId(2)->withIdentity(new Identity('test2', 'test2'))->build();
		$person3 = PersonBuilder::aPerson()->withId(3)->withIdentity(new Identity('test3', 'test3'))->build();

		$godFather1 = new ClassicSponsor(1, $person2, $person, '', '');
		$godFather2 = new ClassicSponsor(2, $person3, $person, '', '');

		$godSon1 = new ClassicSponsor(3, $person, $person2, '', '');
		$godSon2 = new ClassicSponsor(4, $person, $person3, '', '');

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

		$expectedSponsor = new ClassicSponsor(1, $this->person, $this->person, '', '');
		$this->assertEquals($expectedSponsor, $sponsor);
	}

}