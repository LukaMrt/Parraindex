<?php

namespace unit\application\sponsor;

use App\application\sponsor\SponsorDAO;
use App\application\sponsor\SponsorService;
use App\model\person\Person;
use PHPUnit\Framework\TestCase;

class SponsorServiceTest extends TestCase {

    private SponsorService $sponsorService;
    private SponsorDAO $sponsorDAO;

    public function setUp(): void {
        $this->sponsorDAO = $this->createMock(SponsorDAO::class);
        $this->sponsorService = new SponsorService($this->sponsorDAO);
    }

    public function testGetpersonfamilyRetrievesGodFathersAndGodSons() {

        $person = $this->createMock(Person::class);
        $godFather1 = $this->createMock(Person::class);
        $godFather2 = $this->createMock(Person::class);
        $godSon1 = $this->createMock(Person::class);
        $godSon2 = $this->createMock(Person::class);

        $godFathers = [$godFather1, $godFather2];
        $godChildren = [$godSon1, $godSon2];

        $this->sponsorDAO->method('getPersonFamily')
            ->with($this->equalTo(1))
            ->willReturn([
                'person' => $person,
                'godFathers' => $godFathers,
                'godChildren' => $godChildren
            ]);

        $family = $this->sponsorService->getPersonFamily(1);

        $this->assertEquals($family, [
            'person' => $person,
            'godFathers' => $godFathers,
            'godChildren' => $godChildren
        ]);
    }

}