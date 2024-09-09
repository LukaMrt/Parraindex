<?php

namespace App\Infrastructure\old\sponsor;

use App\Application\sponsor\SponsorDAO;
use App\Entity\old\person\characteristic\CharacteristicBuilder;
use App\Entity\old\person\Identity;
use App\Entity\old\person\Person;
use App\Entity\old\person\PersonBuilder;
use App\Entity\old\sponsor\ClassicSponsor;
use App\Entity\old\sponsor\HeartSponsor;
use App\Entity\old\sponsor\Sponsor;
use App\Entity\old\sponsor\SponsorFactory;
use App\Infrastructure\old\database\DatabaseConnection;
use PDOStatement;

/**
 * Mysql Sponsor DAO
 */
class MySqlSponsorDAO implements SponsorDAO
{
    /**
     * @var DatabaseConnection $databaseConnection Database connection
     */
    private DatabaseConnection $databaseConnection;


    /**
     * @param DatabaseConnection $databaseConnection Database connection
     */
    public function __construct(DatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }


    /**
     * Get a person family
     * @param int $personId Id of the person
     * @return array|null
     */
    public function getPersonFamily(int $personId): ?array
    {

        $connection = $this->databaseConnection->getDatabase();

        $personQuery = $connection->prepare(<<<SQL
            SELECT P.*,
                   C.id_characteristic,
                   C.value,
                   C.visibility,
                   T.*,
                   (SELECT MIN(year)
                      FROM Promotion
                          JOIN Student S on Promotion.id_promotion = S.id_promotion
                          JOIN Person P2 on P2.id_person = S.id_person
                      WHERE P2.id_person = P.id_person) as startYear
            FROM Person P
                LEFT JOIN Characteristic C on P.id_person = C.id_person
                LEFT JOIN TypeCharacteristic T on C.id_network = T.id_network
            WHERE P.id_person = :id
            ORDER BY characteristic_order
SQL
        );

        $godFathersQuery = $connection->prepare(<<<SQL
            SELECT P.*
            FROM Person P
            JOIN Sponsor S on P.id_person = S.id_godfather
            WHERE S.id_godson = :id
SQL
        );

        $godChildrenQuery = $connection->prepare(<<<SQL
            SELECT P.*
            FROM Person P
            JOIN Sponsor S on P.id_person = S.id_godson
            WHERE S.id_godfather = :id
SQL
        );

        $godFathersSponsorsQuery = $connection->prepare(<<<SQL
			SELECT S.*,
			       CS.reason,
			       CS.id_sponsor AS id_classic_sponsor,
			       HS.description,
			       HS.id_sponsor AS id_heart_sponsor
			FROM Sponsor S
				LEFT JOIN ClassicSponsor CS on S.id_sponsor = CS.id_sponsor
				LEFT JOIN HeartSponsor HS on S.id_sponsor = HS.id_sponsor
			WHERE S.id_godson = :id
SQL
        );

        $godChildrenSponsorsQuery = $connection->prepare(<<<SQL
			SELECT S.*,
			       CS.reason,
			       CS.id_sponsor AS id_classic_sponsor,
			       HS.description,
			       HS.id_sponsor AS id_heart_sponsor
			FROM Sponsor S
				LEFT JOIN ClassicSponsor CS on S.id_sponsor = CS.id_sponsor
				LEFT JOIN HeartSponsor HS on S.id_sponsor = HS.id_sponsor
			WHERE S.id_godfather = :id
SQL
        );

        $personQuery->execute(['id' => $personId]);
        $godFathersQuery->execute(['id' => $personId]);
        $godChildrenQuery->execute(['id' => $personId]);
        $godFathersSponsorsQuery->execute(['id' => $personId]);
        $godChildrenSponsorsQuery->execute(['id' => $personId]);

        $buildPeople = $this->buildPeople($personQuery);

        if (empty($buildPeople)) {
            $personQuery->closeCursor();
            $godFathersQuery->closeCursor();
            $godChildrenQuery->closeCursor();
            $connection = null;
            return null;
        }

        $person = $buildPeople[0];
        $godFathers = $this->buildPeople($godFathersQuery);
        $godChildren = $this->buildPeople($godChildrenQuery);

        $godFathersSponsors = [];
        $godChildrenSponsors = [];

        while ($row = $godFathersSponsorsQuery->fetch()) {
            $godChild = $person;
            $godFather = null;

            for ($i = 0; $i < count($godFathers); $i++) {
                if ($godFathers[$i]->getId() === $row->id_godfather) {
                    $godFather = $godFathers[$i];
                    break;
                }
            }

            $sponsorType = $row->id_heart_sponsor != null ? 1 : -1;
            $sponsorType = $row->id_classic_sponsor != null ? 0 : $sponsorType;
            $godFathersSponsors[] = SponsorFactory::createSponsor(
                $sponsorType,
                $row->id_sponsor,
                $godFather,
                $godChild,
                $row->sponsorDate ?? '',
                $row->reason ?? $row->description ?? ''
            );
        }

        while ($row = $godChildrenSponsorsQuery->fetch()) {
            $godFather = $person;
            $godChild = null;

            for ($i = 0; $i < count($godChildren); $i++) {
                if ($godChildren[$i]->getId() === $row->id_godson) {
                    $godChild = $godChildren[$i];
                    break;
                }
            }

            $sponsorType = $row->id_heart_sponsor != null ? 1 : -1;
            $sponsorType = $row->id_classic_sponsor != null ? 0 : $sponsorType;
            $godChildrenSponsors[] = SponsorFactory::createSponsor(
                $sponsorType,
                $row->id_sponsor,
                $godFather,
                $godChild,
                $row->sponsorDate ?? '',
                $row->reason ?? $row->description ?? ''
            );
        }

        $personQuery->closeCursor();
        $godFathersQuery->closeCursor();
        $godChildrenQuery->closeCursor();
        $connection = null;
        return [
            'person' => $person,
            'godFathers' => $godFathersSponsors,
            'godChildren' => $godChildrenSponsors
        ];
    }


    /**
     * Build a person
     * @param bool|PDOStatement $query Query
     * @return array
     */
    private function buildPeople(bool|PDOStatement $query): array
    {

        $people = [];

        $currentPerson = null;
        $buffer = [];

        while ($row = $query->fetch()) {
            if ($currentPerson === null) {
                $currentPerson = $row->id_person;
            }

            if ($currentPerson != $row->id_person) {
                $people[] = $this->buildPerson($buffer);
                $buffer = [];
                $currentPerson = $row->id_person;
            }

            $buffer[] = $row;
        }

        if (!empty($buffer)) {
            $people[] = $this->buildPerson($buffer);
        }
        return $people;
    }


    /**
     * Build a person
     * @param array $buffer Buffer
     * @return Person
     */
    private function buildPerson(array $buffer): Person
    {

        $characteristics = [];
        $filterClosure = fn($row) => property_exists($row, 'id_characteristic') && $row->id_characteristic != null;
        $characteristicsBuffer = array_filter($buffer, $filterClosure);

        foreach ($characteristicsBuffer as $row) {
            $characteristics[] = (new CharacteristicBuilder())
                ->withId($row->id_characteristic)
                ->withTitle($row->title)
                ->withType($row->type)
                ->withUrl($row->url)
                ->withImage($row->image)
                ->withVisibility($row->visibility)
                ->withValue($row->value)
                ->build();
        }

        return PersonBuilder::aPerson()
            ->withId($buffer[0]->id_person)
            ->withIdentity(new Identity(
                $buffer[0]->first_name,
                $buffer[0]->last_name,
                $buffer[0]->picture,
                $buffer[0]->birthdate
            ))
            ->withBiography($buffer[0]->biography)
            ->withDescription($buffer[0]->description)
            ->withColor($buffer[0]->banner_color)
            ->withCharacteristics($characteristics)
            ->withStartYear($buffer[0]->startYear ?? -1)
            ->build();
    }


    /**
     * Get a sponsor with id
     * @param int $id Id
     * @return Sponsor|null
     */
    public function getSponsorById(int $id): ?Sponsor
    {

        $connection = $this->databaseConnection->getDatabase();

        $query = $connection->prepare(<<<SQL
			SELECT S.*,
			       CS.reason,
			       CS.id_sponsor AS id_classic_sponsor,
			       HS.description,
			       HS.id_sponsor AS id_heart_sponsor
			FROM Sponsor S
				LEFT JOIN ClassicSponsor CS on S.id_sponsor = CS.id_sponsor
				LEFT JOIN HeartSponsor HS on S.id_sponsor = HS.id_sponsor
			WHERE S.id_sponsor = :id
SQL
        );

        $query->execute(['id' => $id]);
        $sponsor = null;

        if ($row = $query->fetch()) {
            $godFather = PersonBuilder::aPerson()->withId($row->id_godfather)->build();
            $godChild = PersonBuilder::aPerson()->withId($row->id_godson)->build();

            $sponsorType = $row->id_heart_sponsor != null ? 1 : -1;
            $sponsorType = $row->id_classic_sponsor != null ? 0 : $sponsorType;
            $sponsor = SponsorFactory::createSponsor(
                $sponsorType,
                $row->id_sponsor,
                $godFather,
                $godChild,
                $row->sponsorDate ?? '',
                $row->reason ?? $row->description ?? ''
            );
        }

        $query->closeCursor();
        $connection = null;
        return $sponsor;
    }


    /**
     * Get sponsor by people id
     * @param int $godFatherId Godfather id
     * @param int $godChildId Godchild id
     * @return Sponsor|null
     */
    public function getSponsorByPeopleId(int $godFatherId, int $godChildId): ?Sponsor
    {

        $connection = $this->databaseConnection->getDatabase();

        $query = $connection->prepare(<<<SQL
			SELECT S.*,
			       CS.reason,
			       CS.id_sponsor AS id_classic_sponsor,
			       HS.description,
			       HS.id_sponsor AS id_heart_sponsor
			FROM Sponsor S
				LEFT JOIN ClassicSponsor CS on S.id_sponsor = CS.id_sponsor
				LEFT JOIN HeartSponsor HS on S.id_sponsor = HS.id_sponsor
			WHERE S.id_godfather = :godFatherId AND S.id_godson = :godChildId
SQL
        );

        $query->execute(['godFatherId' => $godFatherId, 'godChildId' => $godChildId]);
        $sponsor = null;

        if ($row = $query->fetch()) {
            $godFather = PersonBuilder::aPerson()->withId($row->id_godfather)->build();
            $godChild = PersonBuilder::aPerson()->withId($row->id_godson)->build();

            $sponsorType = $row->id_heart_sponsor != null ? 1 : -1;
            $sponsorType = $row->id_classic_sponsor != null ? 0 : $sponsorType;
            $sponsor = SponsorFactory::createSponsor(
                $sponsorType,
                $row->id_sponsor,
                $godFather,
                $godChild,
                $row->sponsorDate ?? '',
                $row->reason ?? $row->description ?? ''
            );
        }

        $query->closeCursor();
        $connection = null;
        return $sponsor;
    }


    /**
     * Remove a sponsor
     * @param int $id Id
     * @return void
     */
    public function removeSponsor(int $id): void
    {

        $connection = $this->databaseConnection->getDatabase();

        $query = $connection->prepare("DELETE FROM Sponsor WHERE id_sponsor = :id");
        $query->execute(['id' => $id]);

        $query->closeCursor();
        $connection = null;
    }


    /**
     * Add a sponsor
     * @param Sponsor $sponsor Sponsor
     * @return void
     */
    public function addSponsor(Sponsor $sponsor): void
    {

        $connection = $this->databaseConnection->getDatabase();

        $query = $connection->prepare(<<<SQL
                            INSERT INTO Sponsor (id_godfather, id_godson, sponsorDate)
                            VALUES (:godFatherId, :godChildId, :date)
SQL
        );
        $query->execute([
            'godFatherId' => $sponsor->getGodFather()->getId(),
            'godChildId' => $sponsor->getGodChild()->getId(),
            'date' => $sponsor->getDate()->format('Y-m-d')
        ]);

        if ($sponsor instanceof ClassicSponsor) {
            $query = $connection->prepare("INSERT INTO ClassicSponsor (id_sponsor, reason) VALUES (:id, :reason)");
            $query->execute([
                'id' => $connection->lastInsertId(),
                'reason' => $sponsor->getDescription()
            ]);
        } elseif ($sponsor instanceof HeartSponsor) {
            $sql = "INSERT INTO HeartSponsor (id_sponsor, description) VALUES (:id, :description)";
            $query = $connection->prepare($sql);
            $query->execute([
                'id' => $connection->lastInsertId(),
                'description' => $sponsor->getDescription()
            ]);
        }

        $query->closeCursor();
        $connection = null;
    }


    /**
     * Update a sponsor
     * @param Sponsor $sponsor Sponsor
     * @return void
     */
    public function updateSponsor(Sponsor $sponsor): void
    {

        $connection = $this->databaseConnection->getDatabase();

        $query = $connection->prepare("UPDATE Sponsor SET sponsorDate = :date WHERE id_sponsor = :id");
        $query->execute([
            'id' => $sponsor->getId(),
            'date' => $sponsor->getDate()->format('Y-m-d')
        ]);

        if ($sponsor instanceof ClassicSponsor) {
            $query = $connection->prepare("INSERT IGNORE INTO ClassicSponsor VALUES (:id, :reason)");
            $query->execute([
                'id' => $sponsor->getId(),
                'reason' => $sponsor->getDescription()
            ]);
            $query = $connection->prepare("UPDATE ClassicSponsor SET reason = :reason WHERE id_sponsor = :id");
            $query->execute([
                'id' => $sponsor->getId(),
                'reason' => $sponsor->getDescription()
            ]);
            $query = $connection->prepare("DELETE FROM HeartSponsor WHERE id_sponsor = :id");
            $query->execute([
                'id' => $sponsor->getId()
            ]);
        } elseif ($sponsor instanceof HeartSponsor) {
            $query = $connection->prepare("INSERT IGNORE INTO HeartSponsor VALUES (:id, :description)");
            $query->execute([
                'id' => $sponsor->getId(),
                'description' => $sponsor->getDescription()
            ]);
            $query = $connection->prepare("UPDATE HeartSponsor SET description = :description WHERE id_sponsor = :id");
            $query->execute([
                'id' => $sponsor->getId(),
                'description' => $sponsor->getDescription()
            ]);
            $query = $connection->prepare("DELETE FROM ClassicSponsor WHERE id_sponsor = :id");
            $query->execute([
                'id' => $sponsor->getId()
            ]);
        }

        $query->closeCursor();
        $connection = null;
    }
}
