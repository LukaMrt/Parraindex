<?php

namespace App\application\person\characteristic;

use App\model\person\characteristic\Characteristic;
use App\model\person\characteristic\CharacteristicBuilder;
use App\model\person\PersonBuilder;
use Monolog\Test\TestCase;

class CharacteristicTypeServiceTest extends TestCase {

    private CharacteristicTypeDAO $characteristicDAO;
    private CharacteristicTypeService $characteristicTypeService;

    private Characteristic $characteristic;

    public function setUp(): void {

        $this->characteristicDAO = $this->createMock(CharacteristicTypeDAO::class);
        $this->characteristicTypeService = new CharacteristicTypeService($this->characteristicDAO);

        $this->characteristic = (new CharacteristicBuilder())
            ->withId(1)
            ->withType('URL')
            ->withTitle('titre-test')
            ->withImage('image-test')
            ->withUrl('url-test')
            ->withValue('value-test')
            ->withVisibility(true)
            ->build();
    }

    public function testGetallcharacteristictypesReturnsAllTypes() {

        $this->characteristicDAO
            ->method('getAllCharacteristicTypes')
            ->willReturn([$this->characteristic]);

        $characteristics = $this->characteristicTypeService->getAllCharacteristicTypes();

        $this->assertEquals([$this->characteristic], $characteristics);
    }

    public function testGetCharacteristicTypeAndValuesReturnsTypeAndValues() {

        $person = PersonBuilder::aPerson()
                ->build();

        $this->characteristicDAO->method('getAllCharacteristicAndValues')
            ->with($person->getId())
            ->willReturn([$this->characteristic]);

        $characteristic = $this->characteristicTypeService->getAllCharacteristicAndValues($person);

        $this->assertEquals([$this->characteristic], $characteristic);
    }

}