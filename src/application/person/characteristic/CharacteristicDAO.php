<?php

namespace App\application\person\characteristic;

use App\model\person\characteristic\Characteristic;


interface CharacteristicDAO {

	public function getCharacteristic(int $idPerson, String $title ): ?Characteristic;
	
}