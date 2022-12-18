<?php

namespace App\application\sponsor;

use App\application\person\PersonDAO;
use App\model\sponsor\Sponsor;

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

	public function getSponsorById(int $int): ?Sponsor {
		$sponsor = $this->sponsorDAO->getSponsorById($int);

		if ($sponsor === null) {
			return null;
		}

		$godFather = $this->personDAO->getPersonById($sponsor->getGodFather()->getId());
		$godSon = $this->personDAO->getPersonById($sponsor->getGodChild()->getId());
		$sponsor->setGodFather($godFather);
		$sponsor->setGodSon($godSon);
		return $sponsor;
	}

}