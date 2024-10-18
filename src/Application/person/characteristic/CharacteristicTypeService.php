<?php

declare(strict_types=1);

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
    private CharacteristicTypeDAO $characteristicTypeDAO;


    /**
     * @param CharacteristicTypeDAO $characteristicTypeDAO DAO for characteristic types.
     */
    public function __construct(CharacteristicTypeDAO $characteristicTypeDAO)
    {
        $this->characteristicTypeDAO = $characteristicTypeDAO;
    }


    /**
     * Get all the characteristic types
     * @return Characteristic[] of CharacteristicType
     */
    public function getAllCharacteristicTypes(): array
    {
        return $this->characteristicTypeDAO->getAllCharacteristicTypes();
    }


    /**
     * Get all the characteristic types and values
     * The column value is null if the person doesn't have a value for this characteristic
     *
     * @return Characteristic[] of CharacteristicType
     */
    public function getAllCharacteristicAndValues(Person $person): array
    {
        return $this->characteristicTypeDAO->getAllCharacteristicAndValues($person->getId());
    }
}
