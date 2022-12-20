<?php

namespace App\infrastructure\person\characteristic;

use App\application\person\characteristic\CharacteristicTypeDAO;
use App\infrastructure\database\DatabaseConnection;

class MySqlCharacteristicTypeDAO implements CharacteristicTypeDAO {

	private DatabaseConnection $databaseConnection;

	public function __construct(DatabaseConnection $databaseConnection) {
		$this->databaseConnection = $databaseConnection;
	}

	public function getAllCharacteristicTypes(): array {
		$connection = $this->databaseConnection->getDatabase();

		$statement = $connection->prepare("SELECT * FROM TypeCharacteristic");
		$statement->execute();

		$characteristics = array();

		while ($row = $statement->fetch()) {
			$characteristics[] = $row;
		}

		$statement->closeCursor();

		return $characteristics;
	}
}