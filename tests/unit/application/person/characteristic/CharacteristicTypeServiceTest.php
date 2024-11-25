<?php

declare(strict_types=1);

namespace App\Tests\unit\application\person\characteristic;

use App\Application\person\characteristic\CharacteristicTypeDAO;
use App\Application\person\characteristic\CharacteristicTypeService;
use App\Entity\old\person\characteristic\Characteristic;
use App\Entity\old\person\characteristic\CharacteristicBuilder;
use App\Entity\old\person\PersonBuilder;
use Monolog\Test\TestCase;

final class CharacteristicTypeServiceTest extends TestCase
{
    private CharacteristicTypeDAO $characteristicTypeDAO;

    private CharacteristicTypeService $characteristicTypeService;

    private Characteristic $characteristic;


    #[\Override]
    protected function setUp(): void
    {

        $this->characteristicTypeDAO     = $this->createMock(CharacteristicTypeDAO::class);
        $this->characteristicTypeService = new CharacteristicTypeService($this->characteristicTypeDAO);

        $this->characteristic = (new CharacteristicBuilder())
            ->withId(1)
            ->withType('URL')
            ->withTitle('titre-test')
            ->withImage('images-test')
            ->withUrl('url-test')
            ->withValue('value-test')
            ->withVisibility(true)
            ->build();
    }


    public function testGetallcharacteristictypesReturnsAllTypes(): void
    {

        $this->characteristicTypeDAO
            ->method('getAllCharacteristicTypes')
            ->willReturn([$this->characteristic]);

        $characteristics = $this->characteristicTypeService->getAllCharacteristicTypes();

        $this->assertEquals([$this->characteristic], $characteristics);
    }


    public function testGetCharacteristicTypeAndValuesReturnsTypeAndValues(): void
    {

        $person = PersonBuilder::aPerson()
            ->build();

        $this->characteristicTypeDAO->method('getAllCharacteristicAndValues')
            ->with($person->getId())
            ->willReturn([$this->characteristic]);

        $characteristic = $this->characteristicTypeService->getAllCharacteristicAndValues($person);

        $this->assertEquals([$this->characteristic], $characteristic);
    }
}
