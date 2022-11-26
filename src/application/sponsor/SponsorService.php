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

	public function getFamily(int $personId): array {
		$godFathers = array_map(fn($id) => $this->personDAO->getPersonById($id), $this->sponsorDAO->getGodFathers($personId));
		$godSons = array_map(fn($id) => $this->personDAO->getPersonById($id), $this->sponsorDAO->getGodSons($personId));
		return ['godFathers' => $godFathers, 'godChildren' => $godSons];
	}

}