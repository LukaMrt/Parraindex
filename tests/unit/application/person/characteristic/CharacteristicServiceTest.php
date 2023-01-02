<?php

namespace unit\application\person\characteristic;

use App\application\person\characteristic\CharacteristicDAO;
use App\application\person\characteristic\CharacteristicService;
use App\model\person\characteristic\Characteristic;
use App\model\person\characteristic\CharacteristicBuilder;
use PHPUnit\Framework\TestCase;

class CharacteristicServiceTest extends TestCase {

	private Characteristic $characteristic;

	private CharacteristicService $characteristicService;
	private CharacteristicDAO $characteristicDAO;

	public function setUp(): void {

		$this->characteristic = (new CharacteristicBuilder())
			->withId(1)
			->withType('URL')
			->withTitle('titre-test')
			->withImage('image-test')
			->withUrl('url-test')
			->withValue('value-test')
			->withVisibility(true)
			->build();

		$this->characteristicDAO = $this->createMock(CharacteristicDAO::class);
		$this->characteristicService = new CharacteristicService($this->characteristicDAO);
	}

	public function testUpdateCharacteristic() {

		$this->characteristicDAO->expects($this->once())
			->method('updateCharacteristic')
			->with(1, $this->characteristic);

		$this->characteristicService->updateCharacteristic(1, $this->characteristic);
	}

	public function testCreateCharacteristicCreatesCharacteristic() {

		$this->characteristicDAO->expects($this->once())
			->method('createCharacteristic')
			->with(1, $this->characteristic);

		$this->characteristicService->createCharacteristic(1, $this->characteristic);
	}
}