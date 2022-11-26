<?php

namespace App\infrastructure\sponsor;

use App\application\sponsor\SponsorDAO;
use App\infrastructure\database\DatabaseConnection;

class MySqlSponsorDAO implements SponsorDAO {

	private DatabaseConnection $databaseConnection;

	public function __construct(DatabaseConnection $databaseConnection) {
		$this->databaseConnection = $databaseConnection;
	}

	public function getGodFathers(int $personId): array {

		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare("SELECT id_person FROM Person JOIN Sponsor S on Person.id_person = S.id_godfather WHERE S.id_godson = :id");
		$query->bindValue(':id', $personId);
		$query->execute();

		$ids = [];

		while ($row = $query->fetch()) {
			$ids[] = $row->id_person;
		}

		$query->closeCursor();
		$connection = null;
		return $ids;
	}

	public function getGodSons(int $personId): array {

		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare("SELECT id_person FROM Person JOIN Sponsor S on Person.id_person = S.id_godson WHERE S.id_godfather = :id");
		$query->bindValue(':id', $personId);
		$query->execute();

		$ids = [];

		while ($row = $query->fetch()) {
			$ids[] = $row->id_person;
		}

		$query->closeCursor();
		$connection = null;
		return $ids;
	}

}