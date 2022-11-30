<?php

namespace App\application\sponsor;

class SponsorService {

	private SponsorDAO $sponsorDAO;

	public function __construct(SponsorDAO $sponsorDAO) {
		$this->sponsorDAO = $sponsorDAO;
	}

	public function getPersonFamily(int $personId): array {
		return $this->sponsorDAO->getPersonFamily($personId);
	}

}