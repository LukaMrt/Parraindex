<?php

namespace App\application\person\characteristic;

use App\model\person\Person;

class CharacteristicTypeService {

	private CharacteristicTypeDAO $characteristicDAO;

	public function __construct(CharacteristicTypeDAO $characteristicDAO) {
		$this->characteristicDAO = $characteristicDAO;
	}

	/**
	 * Get all the characteristic types
	 * 
	 * @return array of CharacteristicType
	 */
	public function getAllCharacteristicTypes(): array {
		return $this->characteristicDAO->getAllCharacteristicTypes();
	}

	/**
	 * Get all the characteristic types and values
	 * The collumn value is null if the person doesn't have a value for this characteristic
	 * 
	 * @param Person $person
	 * @return array of CharacteristicType
	 */
	public function getAllCharacteristicAndValues(Person $person): array {
		return $this->characteristicDAO->getAllCharacteristicAndValues($person->getId());
	}

}