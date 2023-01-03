<?php

namespace App\infrastructure\person\characteristic;

use App\application\person\characteristic\CharacteristicDAO;
use App\infrastructure\database\DatabaseConnection;
use App\model\person\characteristic\Characteristic;

class MysqlCharacteristicDAO implements CharacteristicDAO
{
    private DatabaseConnection $databaseConnection;

    public function __construct(DatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }

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
