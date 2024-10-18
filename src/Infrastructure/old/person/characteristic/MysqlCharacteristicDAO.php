<?php

declare(strict_types=1);

namespace App\Infrastructure\old\person\characteristic;

use App\Application\person\characteristic\CharacteristicDAO;
use App\Entity\old\person\characteristic\Characteristic;
use App\Infrastructure\old\database\DatabaseConnection;

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
     */
    #[\Override]
    public function updateCharacteristic(int $idPerson, Characteristic $characteristic): void
    {
        $pdo = $this->databaseConnection->getDatabase();

        $statement = $pdo->prepare(<<<SQL
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
     */
    #[\Override]
    public function createCharacteristic(int $idPerson, Characteristic $characteristic): void
    {
        $pdo = $this->databaseConnection->getDatabase();

        $statement = $pdo->prepare(<<<SQL
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
