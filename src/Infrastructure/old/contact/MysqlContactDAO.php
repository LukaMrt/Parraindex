<?php

namespace App\Infrastructure\contact;

use App\Application\contact\ContactDAO;
use App\Entity\contact\Contact;
use App\Entity\contact\ContactType;
use App\Entity\contact\DefaultContact;
use App\Entity\contact\PersonContact;
use App\Entity\contact\SponsorContact;
use App\Entity\person\Identity;
use App\Entity\person\PersonBuilder;
use App\Entity\sponsor\SponsorFactory;
use App\Infrastructure\database\DatabaseConnection;

/**
 * Implementation of the ContactDAO interface for MySQL
 */
class MysqlContactDAO implements ContactDAO
{
    /**
     * @var DatabaseConnection the database connection
     */
    private DatabaseConnection $databaseConnection;


    /**
     * @param DatabaseConnection $databaseConnection the database connection
     */
    public function __construct(DatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }


    /**
     * @param PersonContact $contact the contact to save
     * @return void
     */
    public function savePersonAddContact(PersonContact $contact): void
    {

        $connection = $this->databaseConnection->getDatabase();

        $query = $connection->prepare(<<<SQL
                            INSERT INTO Ticket (type, creation_date, contacter_name, contacter_email, description)
                            VALUES (:type, NOW(), LOWER(:name), LOWER(:email), :description)
SQL
        );
        $query->execute([
            "type" => $contact->getTypeId(),
            "name" => $contact->getContacterName(),
            "email" => $contact->getContacterEmail(),
            "description" => $contact->getMessage()
        ]);

        $ticketId = $connection->lastInsertId();
        $query = $connection->prepare(<<<SQL
                            INSERT INTO EditPerson (id_ticket, first_name, last_name, entry_year)
                            VALUES (:id_ticket, LOWER(:firstname), LOWER(:lastname), :entry_year)
SQL
        );

        $query->execute([
            "id_ticket" => $ticketId,
            "firstname" => $contact->getPerson()->getFirstName(),
            "lastname" => $contact->getPerson()->getLastName(),
            "entry_year" => $contact->getPerson()->getStartYear()
        ]);

        $query->closeCursor();
        $connection = null;
    }


    /**
     * @param PersonContact $contact the contact to save
     * @return void
     */
    public function savePersonRemoveContact(PersonContact $contact): void
    {
        $this->savePersonUpdateContact($contact);
    }


    /**
     * @param PersonContact $contact the contact to save
     * @return void
     */
    public function savePersonUpdateContact(PersonContact $contact): void
    {

        $connection = $this->databaseConnection->getDatabase();

        $query = $connection->prepare(<<<SQL
                            INSERT INTO Ticket (type, creation_date, contacter_name, contacter_email, description)
                            VALUES (:type, NOW(), LOWER(:name), LOWER(:email), :description)
SQL
        );
        $query->execute([
            "type" => $contact->getTypeId(),
            "name" => $contact->getContacterName(),
            "email" => $contact->getContacterEmail(),
            "description" => $contact->getMessage()
        ]);

        $ticketId = $connection->lastInsertId();
        $query = $connection->prepare(<<<SQL
                            INSERT INTO EditPerson (id_ticket, id_person, first_name, last_name, entry_year)
                            VALUES (:id_ticket, :id_person, LOWER(:firstname), LOWER(:lastname), :entry_year)
SQL
        );
        $query->execute([
            "id_ticket" => $ticketId,
            "id_person" => $contact->getPerson()->getId(),
            "firstname" => $contact->getPerson()->getFirstName(),
            "lastname" => $contact->getPerson()->getLastName(),
            "entry_year" => $contact->getPerson()->getStartYear()
        ]);

        $query->closeCursor();
        $connection = null;
    }


    /**
     * @param DefaultContact $contact the contact to save
     * @return void
     */
    public function saveSimpleContact(DefaultContact $contact): void
    {

        $connection = $this->databaseConnection->getDatabase();

        $query = $connection->prepare(<<<SQL
                            INSERT INTO Ticket (type, creation_date, contacter_name, contacter_email, description)
                            VALUES (:type, NOW(), LOWER(:name), LOWER(:email), :description)
SQL
        );
        $query->execute([
            "type" => $contact->getTypeId(),
            "name" => $contact->getContacterName(),
            "email" => $contact->getContacterEmail(),
            "description" => $contact->getMessage()
        ]);

        $query->closeCursor();
        $connection = null;
    }


    /**
     * @param SponsorContact $contact the contact to save
     * @return void
     */
    public function saveSponsorContact(SponsorContact $contact): void
    {

        $connection = $this->databaseConnection->getDatabase();

        $query = $connection->prepare(<<<SQL
                            INSERT INTO Ticket (type, creation_date, contacter_name, contacter_email, description)
                            VALUES (:type, NOW(), LOWER(:name), LOWER(:email), :description)
SQL
        );
        $query->execute([
            "type" => $contact->getTypeId(),
            "name" => $contact->getContacterName(),
            "email" => $contact->getContacterEmail(),
            "description" => $contact->getMessage()
        ]);

        $ticketId = $connection->lastInsertId();
        $query = $connection->prepare(<<<SQL
                            INSERT INTO EditSponsor (id_ticket, id_sponsor, id_godfather,
                                                     id_godson, date, description, type)
                            VALUES (:id_ticket, :id_sponsor, :id_godfather, :id_godson, :date, :description, :type)
SQL
        );
        $date = $contact->getSponsor()->formatDate("Y-m-d");
        $query->execute([
            "id_ticket" => $ticketId,
            "id_sponsor" => $contact->getSponsor()->getId() != -1 ? $contact->getSponsor()->getId() : null,
            "id_godfather" => $contact->getSponsor()->getGodfather()->getId(),
            "id_godson" => $contact->getSponsor()->getGodChild()->getId(),
            "date" => !empty($date) ? $date : null,
            "description" => $contact->getSponsor()->getDescription(),
            "type" => $contact->getSponsor()->getTypeId()
        ]);

        $query->closeCursor();
        $connection = null;
    }


    /**
     * @param PersonContact $contact the contact to save
     * @return void
     */
    public function saveChockingContentContact(PersonContact $contact): void
    {
        $this->savePersonUpdateContact($contact);
    }


    /**
     * @return array the list of all the contacts
     */
    public function getContactList(): array
    {

        $connection = $this->databaseConnection->getDatabase();

        $queryDefault = $connection->prepare(<<<SQL
                        SELECT T.*
                        FROM Ticket T
                            LEFT JOIN EditPerson EP on T.id_ticket = EP.id_ticket
                            LEFT JOIN EditSponsor ES on T.id_ticket = ES.id_ticket
                        WHERE EP.id_ticket IS NULL
                              AND ES.id_ticket IS NULL
                            AND (T.resolution_date IS NULL OR SUBDATE(NOW(), 15) < T.resolution_date);
SQL
        );

        $queryPerson = $connection->prepare(<<<SQL
                        SELECT T.*,
                            EP.id_person,
                            EP.first_name,
                            EP.last_name,
                            EP.entry_year
                        FROM Ticket T
                            LEFT JOIN EditPerson EP on T.id_ticket = EP.id_ticket
                        WHERE EP.id_ticket IS NOT NULL
                            AND (T.resolution_date IS NULL OR SUBDATE(NOW(), 15) < T.resolution_date);
SQL
        );

        $querySponsor = $connection->prepare(<<<SQL
                        SELECT T.*,
                            ES.date,
                            ES.id_sponsor,
                            P.id_person   AS f_id_person,
                            P.last_name   AS f_last_name,
                            P.first_name  AS f_first_name,
                            P2.id_person  AS c_id_person,
                            P2.last_name  AS c_last_name,
                            P2.first_name AS c_first_name,
                            ES.type AS sponsor_type
                        FROM Ticket T
                            LEFT JOIN EditSponsor ES on T.id_ticket = ES.id_ticket
                            JOIN Person P on ES.id_godfather = P.id_person
                            JOIN Person P2 on ES.id_godson = P2.id_person
                        WHERE ES.id_ticket IS NOT NULL
                            AND (T.resolution_date IS NULL OR SUBDATE(NOW(), 15) < T.resolution_date);
SQL
        );

        $queryDefault->execute();
        $queryPerson->execute();
        $querySponsor->execute();

        $contacts = [];

        while ($data = $queryDefault->fetch()) {
            $contacts[] = new DefaultContact(
                $data->id_ticket,
                $data->creation_date,
                $data->resolution_date,
                $data->contacter_name,
                $data->contacter_email,
                ContactType::from($data->type),
                $data->description
            );
        }

        while ($data = $queryPerson->fetch()) {
            $person = PersonBuilder::aPerson()
                ->withId($data->id_person ?? -1)
                ->withIdentity(new Identity($data->first_name, $data->last_name))
                ->withStartYear($data->entry_year ?? -1)
                ->build();
            $contacts[] = new PersonContact(
                $data->id_ticket,
                $data->creation_date,
                $data->resolution_date,
                $data->contacter_name,
                $data->contacter_email,
                ContactType::from($data->type),
                $data->description,
                $person
            );
        }

        while ($data = $querySponsor->fetch()) {
            $godFather = PersonBuilder::aPerson()
                ->withId($data->f_id_person)
                ->withIdentity(new Identity($data->f_first_name, $data->f_last_name))
                ->build();

            $godChild = PersonBuilder::aPerson()
                ->withId($data->c_id_person)
                ->withIdentity(new Identity($data->c_first_name, $data->c_last_name))
                ->build();

            $sponsor = SponsorFactory::createSponsor(
                $data->sponsor_type,
                $data->id_sponsor ?? -1,
                $godFather,
                $godChild,
                $data->date ?? '',
                $data->description
            );

            $contacts[] = new SponsorContact(
                $data->id_ticket,
                $data->creation_date,
                $data->resolution_date,
                $data->contacter_name,
                $data->contacter_email,
                ContactType::from($data->type),
                $data->description,
                $sponsor
            );
        }

        $queryDefault->closeCursor();
        $queryPerson->closeCursor();
        $querySponsor->closeCursor();
        $connection = null;

        usort($contacts, function (Contact $a, Contact $b) {
            return $a->getContactDate() <=> $b->getContactDate();
        });

        return $contacts;
    }


    /**
     * @param int $contactId the id of the contact to close
     * @param int $resolverId the id of the person who resolved the contact
     * @return void
     */
    public function closeContact(int $contactId, int $resolverId): void
    {
        $connection = $this->databaseConnection->getDatabase();

        $query = $connection->prepare(<<<SQL
                            UPDATE Ticket
                            SET id_resolver = :id_resolver, resolution_date = NOW()
                            WHERE id_ticket = :id_ticket
SQL
        );
        $query->execute([
            "id_resolver" => $resolverId,
            "id_ticket" => $contactId
        ]);

        $query->closeCursor();
        $connection = null;
    }
}
