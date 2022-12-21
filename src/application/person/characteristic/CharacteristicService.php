<?php

namespace App\application\person\characteristic;

use App\model\person\characteristic\Characteristic;

class CharacteristicService {

	private CharacteristicDAO $characteristicDAO;

	public function __construct(CharacteristicDAO $characteristicDAO) {
		$this->characteristicDAO = $characteristicDAO;
	}
	
	/**
	 * Update the person's characteristic
	 * 
	 * @param int $idPerson
	 * @param Characteristic $characteristic
	 */
	public function updateCharacteristic(int $idPerson, Characteristic $characteristic): void {
		$this->characteristicDAO->updateCharacteristic($idPerson, $characteristic);
	}

	/**
	 * Create the person's characteristic
	 * 
	 * @param int $idPerson
	 * @param Characteristic $characteristic
	 */
	public function createCharacteristic(int $idPerson, Characteristic $characteristic): void {
		$this->characteristicDAO->createCharacteristic($idPerson, $characteristic);
	}

}