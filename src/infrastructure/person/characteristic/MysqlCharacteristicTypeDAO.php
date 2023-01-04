<?php

namespace App\infrastructure\person\characteristic;

use App\application\person\characteristic\CharacteristicTypeDAO;
use App\infrastructure\database\DatabaseConnection;
use App\model\person\characteristic\Characteristic;
use App\model\person\characteristic\CharacteristicBuilder;

class MysqlCharacteristicTypeDAO implements CharacteristicTypeDAO
{
    private DatabaseConnection $databaseConnection;


    public function __construct(DatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }


    public function getAllCharacteristicTypes(): array
    {
        $connection = $this->databaseConnection->getDatabase();

        $statement = $connection->prepare("SELECT * FROM TypeCharacteristic");
        $statement->execute();

        $characteristics = [];

        while ($row = $statement->fetch()) {
            $characteristics[] = $this->buildCharacteristic($row);
        }

        $statement->closeCursor();

        return $characteristics;
    }


    public function buildCharacteristic($buffer): Characteristic
    {
        return (new CharacteristicBuilder())
            ->withId($buffer->id_network)
            ->withType($buffer->type)
            ->withTitle($buffer->title)
            ->withUrl($buffer->url)
            ->withImage($buffer->image)
            ->withVisibility($buffer->visibility ?? false)
            ->withValue($buffer->value ?? false)
            ->build();
    }


    public function getAllCharacteristicAndValues(int $idPerson): array
    {
        $connection = $this->databaseConnection->getDatabase();

        $statement = $connection->prepare(<<<SQL
                                    SELECT *
									FROM TypeCharacteristic
									    LEFT JOIN (SELECT * FROM Characteristic WHERE id_person = :id_person)
									        AS C USING (id_network);
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
