<?php

namespace App\infrastructure\person\characteristic;

use App\application\person\characteristic\CharacteristicTypeDAO;
use App\infrastructure\database\DatabaseConnection;
use App\model\person\characteristic\Characteristic;
use App\model\person\characteristic\CharacteristicBuilder;

/**
 * Mysql Characteristic Type DAO
 */
class MysqlCharacteristicTypeDAO implements CharacteristicTypeDAO
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
     * Get all the characteristics types
     * @return Characteristic[] The characteristics types
     */
    public function getAllCharacteristicTypes(): array
    {
        $connection = $this->databaseConnection->getDatabase();

        $statement = $connection->prepare(<<<SQL
                                SELECT * FROM TypeCharacteristic
                                ORDER BY characteristic_order
SQL
        );
        $statement->execute();

        $characteristics = [];

        while ($row = $statement->fetch()) {
            $characteristics[] = $this->buildCharacteristic($row);
        }

        $statement->closeCursor();

        return $characteristics;
    }


    /**
     * Build a characteristic
     * @param mixed $row Buffer of the characteristic
     * @return Characteristic The characteristic
     */
    public function buildCharacteristic(mixed $row): Characteristic
    {
        return (new CharacteristicBuilder())
            ->withId($row->id_network)
            ->withType($row->type)
            ->withTitle($row->title)
            ->withUrl($row->url)
            ->withImage($row->image)
            ->withVisibility($row->visibility ?? false)
            ->withValue($row->value ?? false)
            ->build();
    }


    /**
     * Get all characteristics types and values
     * @param int $idPerson Id of the person
     * @return Characteristic[] The characteristics types and values
     */
    public function getAllCharacteristicAndValues(int $idPerson): array
    {
        $connection = $this->databaseConnection->getDatabase();

        $statement = $connection->prepare(<<<SQL
                                    SELECT *
									FROM TypeCharacteristic
									    LEFT JOIN (SELECT * FROM Characteristic WHERE id_person = :id_person)
									        AS C USING (id_network);
                                    ORDER BY characteristic_order
SQL
        );

        $statement->execute([
            ':id_person' => $idPerson
        ]);

        $characteristics = [];

        while ($row = $statement->fetch()) {
            $characteristic = $this->buildCharacteristic($row);
            if ($row->id_characteristic == null) {
                $characteristic->setValue(null);
            }

            $characteristics[] = $characteristic;
        }

        return $characteristics;
    }
}
