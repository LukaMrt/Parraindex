<?php

namespace App\infrastructure\sponsor;

use App\application\sponsor\SponsorDAO;
use App\infrastructure\database\DatabaseConnection;
use App\model\person\characteristic\CharacteristicBuilder;
use App\model\person\Identity;
use App\model\person\Person;
use App\model\person\PersonBuilder;
use App\model\sponsor\ClassicSponsor;
use App\model\sponsor\HeartSponsor;
use App\model\sponsor\Sponsor;
use App\model\sponsor\UnknownSponsor;
use PDOStatement;

class MySqlSponsorDAO implements SponsorDAO {

	private DatabaseConnection $databaseConnection;

	public function __construct(DatabaseConnection $databaseConnection) {
		$this->databaseConnection = $databaseConnection;
	}

	public function getPersonFamily(int $personId): ?array {

		$connection = $this->databaseConnection->getDatabase();

		$personQuery = $connection->prepare(<<<SQL
            SELECT P.*, C.id_characteristic, C.value, C.visibility, T.*, (SELECT MIN(year)
                                                                          FROM Promotion
                                                                              JOIN Student S on Promotion.id_promotion = S.id_promotion
                                                                              JOIN Person P2 on P2.id_person = S.id_person
                                                                          WHERE P2.id_person = P.id_person) as startYear
            FROM Person P
                LEFT JOIN Characteristic C on P.id_person = C.id_person
                LEFT JOIN TypeCharacteristic T on C.id_network = T.id_network
            WHERE P.id_person = :id
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
			SELECT S.*, CS.reason, CS.id_sponsor AS id_classic_sponsor, HS.description, HS.id_sponsor AS id_heart_sponsor
			FROM Sponsor S
				LEFT JOIN ClassicSponsor CS on S.id_sponsor = CS.id_sponsor
				LEFT JOIN HeartSponsor HS on S.id_sponsor = HS.id_sponsor
			WHERE S.id_godson = :id
SQL
		);

		$godChildrenSponsorsQuery = $connection->prepare(<<<SQL
			SELECT S.*, CS.reason, CS.id_sponsor AS id_classic_sponsor, HS.description, HS.id_sponsor AS id_heart_sponsor
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

		if (count($buildPeople) === 0) {
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

			if ($row->id_heart_sponsor != null) {
				$date = property_exists($row, 'sponsorDate') ? $row->sponsorDate ?? '' : '';
				$godFathersSponsors[] = new HeartSponsor($row->id_sponsor, $godFather, $godChild, $date, $row->description);
			} else if ($row->id_classic_sponsor != null) {
				$reason = property_exists($row, 'reason') ? $row->reason ?? '' : '';
				$date = property_exists($row, 'sponsorDate') ? $row->sponsorDate ?? '' : '';
				$godFathersSponsors[] = new ClassicSponsor($row->id_sponsor, $godFather, $godChild, $date, $reason);
			} else {
				$date = property_exists($row, 'sponsorDate') ? $row->sponsorDate ?? '' : '';
				$godFathersSponsors[] = new UnknownSponsor($row->id_sponsor, $godFather, $godChild, $date);
			}

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

			if ($row->id_heart_sponsor != null) {
				$date = property_exists($row, 'sponsorDate') ? $row->sponsorDate ?? '' : '';
				$godChildrenSponsors[] = new HeartSponsor($row->id_sponsor, $godFather, $godChild, $date, $row->description);
			} else if ($row->id_classic_sponsor != null) {
				$reason = property_exists($row, 'reason') ? $row->reason ?? '' : '';
				$date = property_exists($row, 'sponsorDate') ? $row->sponsorDate ?? '' : '';
				$godChildrenSponsors[] = new ClassicSponsor($row->id_sponsor, $godFather, $godChild, $date, $reason);
			} else {
				$date = property_exists($row, 'sponsorDate') ? $row->sponsorDate ?? '' : '';
				$godChildrenSponsors[] = new UnknownSponsor($row->id_sponsor, $godFather, $godChild, $date);
			}

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

    private function buildPerson(array $buffer): Person {

        $characteristics = array();
        $characteristicsBuffer = array_filter($buffer, fn($row) => property_exists($row, 'id_characteristic') && $row->id_characteristic != null);

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
			->withIdentity(new Identity($buffer[0]->first_name, $buffer[0]->last_name, $buffer[0]->picture, $buffer[0]->birthdate))
			->withBiography($buffer[0]->biography)
			->withCharacteristics($characteristics)
			->withStartYear($buffer[0]->startYear ?? -1)
            ->build();
    }

    private function buildPeople(bool|PDOStatement $query): array {

        $people = array();

        $currentPerson = null;
        $buffer = array();

        while ($row = $query->fetch()) {

            if ($currentPerson === null) {
                $currentPerson = $row->id_person;
            }

			if ($currentPerson != $row->id_person) {
				$people[] = $this->buildPerson($buffer);
				$buffer = array();
				$currentPerson = $row->id_person;
			}

			$buffer[] = $row;
		}

		if (0 < count($buffer)) {
			$people[] = $this->buildPerson($buffer);
		}
		return $people;
	}

	public function getSponsorById(int $id): ?Sponsor {

		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare(<<<SQL
			SELECT S.*, CS.reason, CS.id_sponsor AS id_classic_sponsor, HS.description, HS.id_sponsor AS id_heart_sponsor
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

			if ($row->id_heart_sponsor != null) {
				$date = property_exists($row, 'sponsorDate') ? $row->sponsorDate ?? '' : '';
				$sponsor = new HeartSponsor($row->id_sponsor, $godFather, $godChild, $date, $row->description);
			} else if ($row->id_classic_sponsor != null) {
				$reason = property_exists($row, 'reason') ? $row->reason ?? '' : '';
				$date = property_exists($row, 'sponsorDate') ? $row->sponsorDate ?? '' : '';
				$sponsor = new ClassicSponsor($row->id_sponsor, $godFather, $godChild, $date, $reason);
			} else {
				$date = property_exists($row, 'sponsorDate') ? $row->sponsorDate ?? '' : '';
				$sponsor = new UnknownSponsor($row->id_sponsor, $godFather, $godChild, $date);
			}

		}

		$query->closeCursor();
		$connection = null;
		return $sponsor;
	}

	public function getSponsorByPeopleId(int $godFatherId, int $godChildId): ?Sponsor {

		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare(<<<SQL
			SELECT S.*, CS.reason, CS.id_sponsor AS id_classic_sponsor, HS.description, HS.id_sponsor AS id_heart_sponsor
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

			if ($row->id_heart_sponsor != null) {
				$date = property_exists($row, 'sponsorDate') ? $row->sponsorDate ?? '' : '';
				$sponsor = new HeartSponsor($row->id_sponsor, $godFather, $godChild, $date, $row->description);
			} else if ($row->id_classic_sponsor != null) {
				$reason = property_exists($row, 'reason') ? $row->reason ?? '' : '';
				$date = property_exists($row, 'sponsorDate') ? $row->sponsorDate ?? '' : '';
				$sponsor = new ClassicSponsor($row->id_sponsor, $godFather, $godChild, $date, $reason);
			} else {
				$date = property_exists($row, 'sponsorDate') ? $row->sponsorDate ?? '' : '';
				$sponsor = new UnknownSponsor($row->id_sponsor, $godFather, $godChild, $date);
			}

		}

		$query->closeCursor();
		$connection = null;
		return $sponsor;
	}

	public function removeSponsor(int $id): void {

		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare("DELETE FROM Sponsor WHERE id_sponsor = :id");
		$query->execute(['id' => $id]);

		$query->closeCursor();
		$connection = null;
	}

	public function addSponsor(Sponsor $sponsor): void {

		$connection = $this->databaseConnection->getDatabase();

		$query = $connection->prepare("INSERT INTO Sponsor (id_godfather, id_godson, sponsorDate) VALUES (:godFatherId, :godChildId, :date)");
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
		} else if ($sponsor instanceof HeartSponsor) {
			$query = $connection->prepare("INSERT INTO HeartSponsor (id_sponsor, description) VALUES (:id, :description)");
			$query->execute([
				'id' => $connection->lastInsertId(),
				'description' => $sponsor->getDescription()
			]);
		}

		$query->closeCursor();
		$connection = null;
	}

}
