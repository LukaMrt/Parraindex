<?php

namespace App\Application\person\characteristic;

use App\Entity\old\person\characteristic\Characteristic;

/**
 * DAO for the characteristic. It is used to manage the characteristics of a person
 */
interface CharacteristicDAO
{
    /**
     * Update a person's characteristic
     * @param int $idPerson The id of the person related to the characteristic
     * @param Characteristic $characteristic The characteristic to update
     * @return void
     */
    public function updateCharacteristic(int $idPerson, Characteristic $characteristic): void;


    /**
     * Creates a new characteristic for a person
     * @param int $idPerson The id of the person related to the characteristic
     * @param Characteristic $characteristic The characteristic to create
     * @return void
     */
    public function createCharacteristic(int $idPerson, Characteristic $characteristic): void;
}
