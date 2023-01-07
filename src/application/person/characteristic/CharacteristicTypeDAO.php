<?php

namespace App\application\person\characteristic;

use App\model\person\characteristic\Characteristic;

/**
 * DAO for the characteristic type. It is used to manage the characteristic types and their values
 */
interface CharacteristicTypeDAO
{
    /**
     * Retrieves all the characteristic types
     * @return Characteristic[] The characteristic types
     */
    public function getAllCharacteristicTypes(): array;


    /**
     * Retrieves all the characteristic types and their values
     * @param int $idPerson The id of the person
     * @return Characteristic[] The characteristic types and their values
     */
    public function getAllCharacteristicAndValues(int $idPerson): array;
}
