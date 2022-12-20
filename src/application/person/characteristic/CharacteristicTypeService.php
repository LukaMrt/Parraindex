<?php

namespace App\application\person\characteristic;

class CharacteristicTypeService {

	private CharacteristicTypeDAO $characteristicDAO;

	public function __construct(CharacteristicTypeDAO $characteristicDAO) {
		$this->characteristicDAO = $characteristicDAO;
	}

	/**
	 * Get all the characteristic types
	 * 
	 * @return array
	 */
	public function getAllCharacteristicTypes(): array {
		return $this->characteristicDAO->getAllCharacteristicTypes();
	}

}