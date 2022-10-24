<?php

namespace App\infrastructure\database;

use PDO;

class DatabaseConnection {

	private PDO $database;

	public function __construct() {
		$this->connect();
	}

	private function connect(): void {

		[
			'driver' => $driver,
			'host' => $host,
			'port' => $port,
			'database' => $database,
			'username' => $username,
			'password' => $password,
		] = $_ENV;

		$this->database = new PDO("$driver:host=$host; dbname=$database; port=$port; charset=utf8", $username, $password);

		$this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
		$this->database->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);

	}

	public function getDatabase(): PDO {
		return $this->database;
	}

}