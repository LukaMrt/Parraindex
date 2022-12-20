<?php

namespace App\infrastructure\person\characteristic;

use App\application\person\characteristic\CharacteristicDAO;
use App\infrastructure\database\DatabaseConnection;
use App\model\person\characteristic\Characteristic;
use App\model\person\characteristic\CharacteristicBuilder;

class MySqlCharacteristicDAO implements CharacteristicDAO{
	
	private DatabaseConnection $databaseConnection;

	public function __construct(DatabaseConnection $databaseConnection) {
		$this->databaseConnection = $databaseConnection;
	}

    public function getCharacteristic($idPerson, $title): ?Characteristic {
        $connection = $this->databaseConnection->getDatabase();

		$statement = $connection->prepare("SELECT * FROM Characteristic
										JOIN TypeCharacteristic using(id_network)
										WHERE id_person = :idPerson AND title = :title");
		$statement->execute([
			'idPerson' => $idPerson,
			'title' => $title
		]);
		
		$row = $statement->fetch();

		if (!$row)
			return null;

		$characteristic = (new CharacteristicBuilder())
			->withId($row->id_characteristic)
			->withTitle($row->title)
			->withType($row->type)
			->withUrl($row->url)
			->withImage($row->image)
			->withVisibility($row->visibility)
			->withValue($row->value)
			->build();

		return $characteristic;

    }
}