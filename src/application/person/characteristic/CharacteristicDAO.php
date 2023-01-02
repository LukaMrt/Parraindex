<?php

namespace App\application\person\characteristic;

use App\model\person\characteristic\Characteristic;


interface CharacteristicDAO {

	public function updateCharacteristic(int $idPerson, Characteristic $characteristic): void;

	public function createCharacteristic(int $idPerson, Characteristic $characteristic): void;

}