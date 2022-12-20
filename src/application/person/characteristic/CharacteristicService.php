<?php

namespace App\application\person\characteristic;

use App\model\person\characteristic\Characteristic;

class CharacteristicService {

	private CharacteristicDAO $characteristicDAO;

	public function __construct(CharacteristicDAO $characteristicDAO) {
		$this->characteristicDAO = $characteristicDAO;
	}
	
	/**
	 * Get the person's characteristic by the person's id and the characteristic's title
	 * 
	 * @param int $idPerson
	 * @param int $idCharacteristic
	 */
	public function getCharacteristic(int $idPerson, String $title): ?Characteristic {
		return $this->characteristicDAO->getCharacteristic($idPerson, $title);
	}

}