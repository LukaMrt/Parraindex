<?php

declare(strict_types=1);

namespace App\Infrastructure\old\contact;

use App\Application\contact\ContactDAO;
use App\Entity\Contact\Type;
use App\Entity\old\contact\Contact;
use App\Entity\old\contact\DefaultContact;
use App\Entity\old\contact\PersonContact;
use App\Entity\old\contact\SponsorContact;
use App\Entity\old\person\Identity;
use App\Entity\old\person\PersonBuilder;
use App\Entity\old\sponsor\SponsorFactory;
use App\Infrastructure\old\database\DatabaseConnection;

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
     * @param PersonContact $personContact the contact to save
     */
    #[\Override]
    public function savePersonAddContact(PersonContact $personContact): void
    {

        $pdo = $this->databaseConnection->getDatabase();

        $query = $pdo->prepare(<<<SQL
                            INSERT INTO Ticket (type, creation_date, contacter_name, contacter_email, description)
                            VALUES (:type, NOW(), LOWER(:name), LOWER(:email), :description)
SQL
        );
        $query->execute([
            "type" => $personContact->getTypeId(),
            "name" => $personContact->getContacterName(),
            "email" => $personContact->getContacterEmail(),
            "description" => $personContact->getMessage()
        ]);

        $ticketId = $pdo->lastInsertId();
        $query    = $pdo->prepare(<<<SQL
                            INSERT INTO EditPerson (id_ticket, first_name, last_name, entry_year)
                            VALUES (:id_ticket, LOWER(:firstname), LOWER(:lastname), :entry_year)
SQL
        );

        $query->execute([
            "id_ticket" => $ticketId,
            "firstname" => $personContact->getPerson()->getFirstName(),
            "lastname" => $personContact->getPerson()->getLastName(),
            "entry_year" => $personContact->getPerson()->getStartYear()
        ]);

        $query->closeCursor();

    }


    /**
     * @param PersonContact $personContact the contact to save
     */
    #[\Override]
    public function savePersonRemoveContact(PersonContact $personContact): void
    {
        $this->savePersonUpdateContact($personContact);
    }


    /**
     * @param PersonContact $personContact the contact to save
     */
    #[\Override]
    public function savePersonUpdateContact(PersonContact $personContact): void
    {

        $pdo = $this->databaseConnection->getDatabase();

        $query = $pdo->prepare(<<<SQL
                            INSERT INTO Ticket (type, creation_date, contacter_name, contacter_email, description)
                            VALUES (:type, NOW(), LOWER(:name), LOWER(:email), :description)
SQL
        );
        $query->execute([
            "type" => $personContact->getTypeId(),
            "name" => $personContact->getContacterName(),
            "email" => $personContact->getContacterEmail(),
            "description" => $personContact->getMessage()
        ]);

        $ticketId = $pdo->lastInsertId();
        $query    = $pdo->prepare(<<<SQL
                            INSERT INTO EditPerson (id_ticket, id_person, first_name, last_name, entry_year)
                            VALUES (:id_ticket, :id_person, LOWER(:firstname), LOWER(:lastname), :entry_year)
SQL
        );
        $query->execute([
            "id_ticket" => $ticketId,
            "id_person" => $personContact->getPerson()->getId(),
            "firstname" => $personContact->getPerson()->getFirstName(),
            "lastname" => $personContact->getPerson()->getLastName(),
            "entry_year" => $personContact->getPerson()->getStartYear()
        ]);

        $query->closeCursor();

    }


    /**
     * @param DefaultContact $defaultContact the contact to save
     */
    #[\Override]
    public function saveSimpleContact(DefaultContact $defaultContact): void
    {

        $pdo = $this->databaseConnection->getDatabase();

        $query = $pdo->prepare(<<<SQL
                            INSERT INTO Ticket (type, creation_date, contacter_name, contacter_email, description)
                            VALUES (:type, NOW(), LOWER(:name), LOWER(:email), :description)
SQL
        );
        $query->execute([
            "type" => $defaultContact->getTypeId(),
            "name" => $defaultContact->getContacterName(),
            "email" => $defaultContact->getContacterEmail(),
            "description" => $defaultContact->getMessage()
        ]);

        $query->closeCursor();

    }


    /**
     * @param SponsorContact $sponsorContact the contact to save
     */
    #[\Override]
    public function saveSponsorContact(SponsorContact $sponsorContact): void
    {

        $pdo = $this->databaseConnection->getDatabase();

        $query = $pdo->prepare(<<<SQL
                            INSERT INTO Ticket (type, creation_date, contacter_name, contacter_email, description)
                            VALUES (:type, NOW(), LOWER(:name), LOWER(:email), :description)
SQL
        );
        $query->execute([
            "type" => $sponsorContact->getTypeId(),
            "name" => $sponsorContact->getContacterName(),
            "email" => $sponsorContact->getContacterEmail(),
            "description" => $sponsorContact->getMessage()
        ]);

        $ticketId = $pdo->lastInsertId();
        $query    = $pdo->prepare(<<<SQL
                            INSERT INTO EditSponsor (id_ticket, id_sponsor, id_godfather,
                                                     id_godson, date, description, type)
                            VALUES (:id_ticket, :id_sponsor, :id_godfather, :id_godson, :date, :description, :type)
SQL
        );
        $date     = $sponsorContact->getSponsor()->formatDate("Y-m-d");
        $query->execute([
            "id_ticket" => $ticketId,
            "id_sponsor" => $sponsorContact->getSponsor()->getId() != -1 ? $sponsorContact->getSponsor()->getId() : null,
            "id_godfather" => $sponsorContact->getSponsor()->getGodfather()->getId(),
            "id_godson" => $sponsorContact->getSponsor()->getGodChild()->getId(),
            "date" => $date === '' || $date === '0' ? null : $date,
            "description" => $sponsorContact->getSponsor()->getDescription(),
            "type" => $sponsorContact->getSponsor()->getTypeId()
        ]);

        $query->closeCursor();

    }


    /**
     * @param PersonContact $personContact the contact to save
     */
    #[\Override]
    public function saveChockingContentContact(PersonContact $personContact): void
    {
        $this->savePersonUpdateContact($personContact);
    }


    /**
     * @return array the list of all the contacts
     */
    #[\Override]
    public function getContactList(): array
    {

        $pdo = $this->databaseConnection->getDatabase();

        $queryDefault = $pdo->prepare(<<<SQL
                        SELECT T.*
                        FROM Ticket T
                            LEFT JOIN EditPerson EP on T.id_ticket = EP.id_ticket
                            LEFT JOIN EditSponsor ES on T.id_ticket = ES.id_ticket
                        WHERE EP.id_ticket IS NULL
                              AND ES.id_ticket IS NULL
                            AND (T.resolution_date IS NULL OR SUBDATE(NOW(), 15) < T.resolution_date);
SQL
        );

        $queryPerson = $pdo->prepare(<<<SQL
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

        $querySponsor = $pdo->prepare(<<<SQL
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
                Type::from($data->type),
                $data->description
            );
        }

        while ($data = $queryPerson->fetch()) {
            $person     = PersonBuilder::aPerson()
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
                Type::from($data->type),
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
                Type::from($data->type),
                $data->description,
                $sponsor
            );
        }

        $queryDefault->closeCursor();
        $queryPerson->closeCursor();
        $querySponsor->closeCursor();

        usort($contacts, function (Contact $a, Contact $b): int {
            return $a->getContactDate() <=> $b->getContactDate();
        });

        return $contacts;
    }


    /**
     * @param int $contactId the id of the contact to close
     * @param int $resolverId the id of the person who resolved the contact
     */
    #[\Override]
    public function closeContact(int $contactId, int $resolverId): void
    {
        $pdo = $this->databaseConnection->getDatabase();

        $query = $pdo->prepare(<<<SQL
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

    }
}
