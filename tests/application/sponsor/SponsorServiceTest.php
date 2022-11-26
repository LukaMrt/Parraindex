<?php

namespace application\person;

use App\application\person\PersonDAO;
use App\application\sponsor\SponsorDAO;
use App\application\sponsor\SponsorService;
use App\model\person\Person;
use PHPUnit\Framework\TestCase;

class SponsorServiceTest extends TestCase {

	private SponsorService $sponsorService;
	private SponsorDAO $sponsorDAO;
	private PersonDAO $personDAO;

	public function setUp(): void {
		$this->sponsorDAO = $this->createMock(SponsorDAO::class);
		$this->personDAO = $this->createMock(PersonDAO::class);
		$this->sponsorService = new SponsorService($this->sponsorDAO, $this->personDAO);
	}

	public function testGetFamilyRetrievesGodFathersAndGodSons() {

		$godFather1 = $this->createMock(Person::class);
		$godFather2 = $this->createMock(Person::class);
		$godSon1 = $this->createMock(Person::class);
		$godSon2 = $this->createMock(Person::class);

		$godFathers = [$godFather1, $godFather2];
		$godSons = [$godSon1, $godSon2];

		$this->sponsorDAO->method('getGodFathers')
			->with($this->equalTo(1))
			->willReturn([2, 3]);
		$this->sponsorDAO->method('getGodSons')
			->with($this->equalTo(1))
			->willReturn([4, 5]);

		$this->personDAO->method('getPersonById')
			->withConsecutive([2], [3], [4], [5])
			->willReturnOnConsecutiveCalls($godFather1, $godFather2, $godSon1, $godSon2);

		$family = $this->sponsorService->getFamily(1);

		$this->assertEquals($godFathers, $family['godFathers']);
		$this->assertEquals($godSons, $family['godChildren']);
	}

}