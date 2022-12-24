<?php

namespace App\infrastructure\database\contact;

use App\application\contact\ContactDAO;
use App\infrastructure\database\DatabaseConnection;
use App\model\contact\Contact;
use App\model\person\Person;
use App\model\sponsor\Sponsor;

class MysqlContactDAO implements ContactDAO {

	private DatabaseConnection $databaseConnection;

	public function __construct(DatabaseConnection $databaseConnection) {
		$this->databaseConnection = $databaseConnection;
	}

	public function savePersonAddContact(Person $person, Contact $contact): void {

		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare("INSERT INTO Ticket (type, creation_date, contacter_name, contacter_email, description) VALUES (:type, NOW(), :name, :email, :description)");
		$query->execute([
			"type" => $contact->getTypeId(),
			"name" => $contact->getName(),
			"email" => $contact->getEmail(),
			"description" => $contact->getDescription()
		]);

		$ticketId = $connection->lastInsertId();
		$query = $connection->prepare("INSERT INTO EditPerson (id_ticket, first_name, last_name, entry_year) VALUES (:id_ticket, :firstname, :lastname, :entry_year)");
		$query->execute([
			"id_ticket" => $ticketId,
			"firstname" => $person->getFirstName(),
			"lastname" => $person->getLastName(),
			"entry_year" => $person->getStartYear()
		]);

		$query->closeCursor();
		$connection = null;
	}

	public function savePersonRemoveContact(?Person $person, Contact $contact): void {

		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare("INSERT INTO Ticket (type, creation_date, contacter_name, contacter_email, description) VALUES (:type, NOW(), :name, :email, :description)");
		$query->execute([
			"type" => $contact->getTypeId(),
			"name" => $contact->getName(),
			"email" => $contact->getEmail(),
			"description" => $contact->getDescription()
		]);

		$ticketId = $connection->lastInsertId();
		$query = $connection->prepare("INSERT INTO EditPerson (id_ticket, id_person, first_name, last_name) VALUES (:id_ticket, :id_person, :firstname, :lastname)");
		$query->execute([
			"id_ticket" => $ticketId,
			"id_person" => $person->getId(),
			"firstname" => $person->getFirstName(),
			"lastname" => $person->getLastName()
		]);

		$query->closeCursor();
		$connection = null;
	}

	public function savePersonUpdateContact(?Person $person, Contact $contact): void {

		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare("INSERT INTO Ticket (type, creation_date, contacter_name, contacter_email, description) VALUES (:type, NOW(), :name, :email, :description)");
		$query->execute([
			"type" => $contact->getTypeId(),
			"name" => $contact->getName(),
			"email" => $contact->getEmail(),
			"description" => $contact->getDescription()
		]);

		$ticketId = $connection->lastInsertId();
		$query = $connection->prepare("INSERT INTO EditPerson (id_ticket, id_person, first_name, last_name, entry_year) VALUES (:id_ticket, :id_person, :firstname, :lastname, :entry_year)");
		$query->execute([
			"id_ticket" => $ticketId,
			"id_person" => $person->getId(),
			"firstname" => $person->getFirstName(),
			"lastname" => $person->getLastName(),
			"entry_year" => $person->getStartYear()
		]);

		$query->closeCursor();
		$connection = null;
	}

	public function saveSimpleContact(Contact $contact): void {

		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare("INSERT INTO Ticket (type, creation_date, contacter_name, contacter_email, description) VALUES (:type, NOW(), :name, :email, :description)");
		$query->execute([
			"type" => $contact->getTypeId(),
			"name" => $contact->getName(),
			"email" => $contact->getEmail(),
			"description" => $contact->getDescription()
		]);

		$query->closeCursor();
		$connection = null;
	}

	public function saveSponsorContact(Contact $contact, Sponsor $sponsor): void {

		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare("INSERT INTO Ticket (type, creation_date, contacter_name, contacter_email, description) VALUES (:type, NOW(), :name, :email, :description)");
		$query->execute([
			"type" => $contact->getTypeId(),
			"name" => $contact->getName(),
			"email" => $contact->getEmail(),
			"description" => $contact->getDescription()
		]);

		$ticketId = $connection->lastInsertId();
		$query = $connection->prepare("INSERT INTO EditSponsor (id_ticket, id_godfather, id_godson, date, description, type) VALUES (:id_ticket, :id_godfather, :id_godson, :date, :description, :type)");
		$query->execute([
			"id_ticket" => $ticketId,
			"id_godfather" => $sponsor->getGodfather()->getId(),
			"id_godson" => $sponsor->getGodChild()->getId(),
			"date" => $sponsor->getDate()->format("Y-m-d"),
			"description" => $sponsor->getDescription(),
			"type" => $sponsor->getTypeId()
		]);

		$query->closeCursor();
		$connection = null;
	}

	public function saveChockingContentContact(Person $person, Contact $contact): void {

		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare("INSERT INTO Ticket (type, creation_date, contacter_name, contacter_email, description) VALUES (:type, NOW(), :name, :email, :description)");
		$query->execute([
			"type" => $contact->getTypeId(),
			"name" => $contact->getName(),
			"email" => $contact->getEmail(),
			"description" => $contact->getDescription()
		]);

		$ticketId = $connection->lastInsertId();
		$query = $connection->prepare("INSERT INTO EditPerson (id_ticket, id_person, first_name, last_name, entry_year) VALUES (:id_ticket, :id_person, :firstname, :lastname, :entry_year)");
		$query->execute([
			"id_ticket" => $ticketId,
			"id_person" => $person->getId(),
			"firstname" => $person->getFirstName(),
			"lastname" => $person->getLastName(),
			"entry_year" => $person->getStartYear()
		]);

		$query->closeCursor();
		$connection = null;
	}

}