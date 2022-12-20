<?php

namespace App\infrastructure\database\contact;

use App\application\contact\ContactDAO;
use App\infrastructure\database\DatabaseConnection;
use App\model\contact\Contact;

class MysqlContactDAO implements ContactDAO {

	private DatabaseConnection $databaseConnection;

	public function __construct(DatabaseConnection $databaseConnection) {
		$this->databaseConnection = $databaseConnection;
	}

	public function saveContact(Contact $contact): void {

		$connection = $this->databaseConnection->getDatabase();

		$connection->exec("INSERT INTO Ticket (creation_date) VALUES (NOW())");

		$ticketId = $connection->lastInsertId();
		$statement = $connection->prepare("INSERT INTO Contact (id_ticket, description, contacter_name, email, type) VALUES (:id_ticket, :description, :name, :email, :type)");
		$statement->execute([
			"id_ticket" => $ticketId,
			"description" => $contact->getDescription(),
			"name" => $contact->getName(),
			"email" => $contact->getEmail(),
			"type" => $contact->getType()
		]);

		$connection = null;
	}

}