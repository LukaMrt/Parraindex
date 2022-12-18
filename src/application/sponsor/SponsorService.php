<?php

namespace App\application\sponsor;

use App\application\person\PersonDAO;

class SponsorService {

	private SponsorDAO $sponsorDAO;
	private PersonDAO $personDAO;

	public function __construct(SponsorDAO $sponsorDAO, PersonDAO $personDAO) {
		$this->sponsorDAO = $sponsorDAO;
		$this->personDAO = $personDAO;
	}

	public function getPersonFamily(int $personId): array {
		return $this->sponsorDAO->getPersonFamily($personId);
	}

	public function getSponsorById(int $int): ?array {
		$sponsor = $this->sponsorDAO->getSponsorById($int);

		if ($sponsor === null) {
			return null;
		}

		return [
			'sponsor' => $sponsor,
			'godFather' => $this->personDAO->getPersonById($sponsor->getGodFather()->getId()),
			'godChild' => $this->personDAO->getPersonById($sponsor->getGodSon()->getId())
		];
	}

}