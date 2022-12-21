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

	public function updateCharacteristic(int $idPerson, Characteristic $characteristic): void{
		$connection = $this->databaseConnection->getDatabase();

		$statement = $connection->prepare("UPDATE Characteristic SET 
											value = :value,
											visibility = :visibility
											WHERE id_person = :idPerson AND id_network = :idNetwork");
		$statement->execute([
			'idPerson' => $idPerson,
			'idNetwork' => $characteristic->getId(),
			'value' => $characteristic->getValue(),
			'visibility' => $characteristic->getVisible() ? '1' : '0'
		]);
	}

	public function createCharacteristic(int $idPerson, Characteristic $characteristic): void{
		$connection = $this->databaseConnection->getDatabase();

		$statement = $connection->prepare("INSERT INTO Characteristic (id_person, id_network, value, visibility)
											VALUES (:idPerson, :idNetwork, :value, :visibility)");
		$statement->execute([
			'idPerson' => $idPerson,
			'idNetwork' => $characteristic->getId(),
			'value' => $characteristic->getValue(),
			'visibility' => $characteristic->getVisible() ? '1' : '0'
		]);
	}
}