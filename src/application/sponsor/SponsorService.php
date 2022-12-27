<?php

namespace App\application\sponsor;

use App\application\person\PersonDAO;
use App\model\sponsor\ClassicSponsor;
use App\model\sponsor\HeartSponsor;
use App\model\sponsor\Sponsor;

class SponsorService {

	private SponsorDAO $sponsorDAO;
	private PersonDAO $personDAO;

	public function __construct(SponsorDAO $sponsorDAO, PersonDAO $personDAO) {
		$this->sponsorDAO = $sponsorDAO;
		$this->personDAO = $personDAO;
	}

	public function getPersonFamily(int $personId): ?array {
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

	public function removeSponsor(int $id): void {
		$this->sponsorDAO->removeSponsor($id);
	}

	public function addSponsor(Sponsor $sponsor): void {
		$this->sponsorDAO->addSponsor($sponsor);
	}

	public function getSponsor(int $id): ?Sponsor {
		return $this->sponsorDAO->getSponsorById($id);
	}

	public function createSponsor(mixed $id, array $parameters): void {

		$godFather = $this->personDAO->getPersonById($parameters['godFatherId']);
		$godSon = $this->personDAO->getPersonById($parameters['godChildId']);

		$sponsor = $this->sponsorDAO->getSponsorByPeopleId($godFather->getId(), $godSon->getId());

		if ($sponsor !== null) {
			return;
		}

		if ($parameters['sponsorType'] === '0') {
			$sponsor = new ClassicSponsor(-1, $godFather, $godSon, $parameters['sponsorDate'], $parameters['description']);
		} else {
			$sponsor = new HeartSponsor(-1, $godFather, $godSon, $parameters['sponsorDate'], $parameters['description']);
		}

		$this->sponsorDAO->addSponsor($sponsor);
	}

	public function updateSponsor(mixed $id, array $parameters) {

		$sponsor = $this->sponsorDAO->getSponsorById($id);

		if ($sponsor === null) {
			return;
		}

		$godFather = $sponsor->getGodFather();
		$godSon = $sponsor->getGodChild();

		if ($parameters['sponsorType'] === '2') {
			return;
		}

		if ($parameters['sponsorType'] === '0') {
			$sponsor = new ClassicSponsor($id, $godFather, $godSon, $parameters['sponsorDate'], $parameters['description']);
		} else {
			$sponsor = new HeartSponsor($id, $godFather, $godSon, $parameters['sponsorDate'], $parameters['description']);
		}

		$this->sponsorDAO->updateSponsor($sponsor);

	}

}