<?php

namespace App\infrastructure\person\characteristic;

use App\application\person\characteristic\CharacteristicDAO;
use App\infrastructure\database\DatabaseConnection;
use App\model\person\characteristic\Characteristic;

/**
 * Mysql Characteristic DAO
 */
class MysqlCharacteristicDAO implements CharacteristicDAO
{
    /**
     * @var DatabaseConnection $databaseConnection Database connection
     */
    private DatabaseConnection $databaseConnection;


    /**
     * @param DatabaseConnection $databaseConnection Database connection
     */
    public function __construct(DatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }


    /**
     * Update a characteristic
     * @param int $idPerson Id of the person
     * @param Characteristic $characteristic Characteristic
     * @return void
     */
    public function updateCharacteristic(int $idPerson, Characteristic $characteristic): void
    {
        $connection = $this->databaseConnection->getDatabase();

        $statement = $connection->prepare(<<<SQL
                                    UPDATE Characteristic
                                    SET value = :value, visibility = :visibility
									WHERE id_person = :idPerson
									  AND id_network = :idNetwork
SQL
        );
        $statement->execute([
            'idPerson' => $idPerson,
            'idNetwork' => $characteristic->getId(),
            'value' => $characteristic->getValue(),
            'visibility' => $characteristic->getVisible() ? '1' : '0'
        ]);
    }


    /**
     * Create a characteristic
     * @param int $idPerson Id of the person
     * @param Characteristic $characteristic Characteristic
     * @return void
     */
    public function createCharacteristic(int $idPerson, Characteristic $characteristic): void
    {
        $connection = $this->databaseConnection->getDatabase();

        $statement = $connection->prepare(<<<SQL
                                    INSERT INTO Characteristic (id_person, id_network, value, visibility)
									VALUES (:idPerson, :idNetwork, :value, :visibility)
SQL
        );
        $statement->execute([
            'idPerson' => $idPerson,
            'idNetwork' => $characteristic->getId(),
            'value' => $characteristic->getValue(),
            'visibility' => $characteristic->getVisible() ? '1' : '0'
        ]);
    }
}
