<?php

namespace App\application\person\characteristic;

interface CharacteristicTypeDAO
{

    public function getAllCharacteristicTypes(): array;


    public function getAllCharacteristicAndValues(int $idPerson): array;

}
