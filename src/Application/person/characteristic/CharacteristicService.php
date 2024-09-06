<?php

namespace App\Application\person\characteristic;

use App\Entity\person\characteristic\Characteristic;

/**
 * Service to manage the characteristics
 */
class CharacteristicService
{
    /**
     * @var CharacteristicDAO DAO for the characteristics
     */
    private CharacteristicDAO $characteristicDAO;


    /**
     * @param CharacteristicDAO $characteristicDAO DAO for the characteristics
     */
    public function __construct(CharacteristicDAO $characteristicDAO)
    {
        $this->characteristicDAO = $characteristicDAO;
    }


    /**
     * Update the person's characteristic
     * @param int $idPerson Id of the person related to the characteristic
     * @param Characteristic $characteristic The characteristic to update
     */
    public function updateCharacteristic(int $idPerson, Characteristic $characteristic): void
    {
        $this->characteristicDAO->updateCharacteristic($idPerson, $characteristic);
    }


    /**
     * Create the person's characteristic
     * @param int $idPerson Id of the person related to the characteristic
     * @param Characteristic $characteristic The characteristic to create
     */
    public function createCharacteristic(int $idPerson, Characteristic $characteristic): void
    {
        $this->characteristicDAO->createCharacteristic($idPerson, $characteristic);
    }
}
