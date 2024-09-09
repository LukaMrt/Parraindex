<?php

namespace App\Application\person\characteristic;

use App\Entity\old\person\characteristic\Characteristic;
use App\Entity\old\person\Person;

/**
 * Service to manage the characteristic types.
 */
class CharacteristicTypeService
{
    /**
     * @var CharacteristicTypeDAO DAO for characteristic types.
     */
    private CharacteristicTypeDAO $characteristicDAO;


    /**
     * @param CharacteristicTypeDAO $characteristicDAO DAO for characteristic types.
     */
    public function __construct(CharacteristicTypeDAO $characteristicDAO)
    {
        $this->characteristicDAO = $characteristicDAO;
    }


    /**
     * Get all the characteristic types
     * @return Characteristic[] of CharacteristicType
     */
    public function getAllCharacteristicTypes(): array
    {
        return $this->characteristicDAO->getAllCharacteristicTypes();
    }


    /**
     * Get all the characteristic types and values
     * The column value is null if the person doesn't have a value for this characteristic
     *
     * @param Person $person
     * @return Characteristic[] of CharacteristicType
     */
    public function getAllCharacteristicAndValues(Person $person): array
    {
        return $this->characteristicDAO->getAllCharacteristicAndValues($person->getId());
    }
}
