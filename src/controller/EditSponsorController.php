<?php

namespace App\controller;

use App\application\person\PersonService;
use App\application\sponsor\SponsorService;
use App\infrastructure\router\Router;
use App\model\account\PrivilegeType;
use Twig\Environment;

class EditSponsorController extends Controller {

	private SponsorService $sponsorService;

	public function __construct(Environment $twig, Router $router, PersonService $personService, SponsorService $sponsorService) {
		parent::__construct($twig, $router, $personService);
		$this->sponsorService = $sponsorService;
	}

	public function get(Router $router, array $parameters): void {

		if (empty($_SESSION) || PrivilegeType::fromString($_SESSION['privilege']) !== PrivilegeType::ADMIN) {
			header('Location: ' . $router->url('error', ['error' => 403]));
			die();
		}

		$sponsor = $this->sponsorService->getSponsor($parameters['id']);
		$people = $this->personService->getAllPeople();
		usort($people, fn($a, $b) => $a->getLastName() !== '?' && $a->getLastName() < $b->getLastName() ? -1 : 1);
		$closure = fn($person) => ['id' => $person->getId(), 'title' => $person->getLastName() . ' ' . $person->getFirstName()];
		$people = array_map($closure, $people);
		$people2 = $people;

		$sponsorTypes = [['id' => 0, 'title' => 'Parrainage IUT'], ['id' => 1, 'title' => 'Parrainage de coeur'], ['id' => 2, 'title' => 'Type inconnu']];

		if ($sponsor !== null) {
			$godFather = $this->personService->getPersonById($sponsor->getGodFather()->getId());
			$godChild = $this->personService->getPersonById($sponsor->getGodChild()->getId());
			$people = [['id' => $godFather->getId(), 'title' => $godFather->getLastName() . ' ' . $godFather->getFirstName()]];
			$people2 = [['id' => $godChild->getId(), 'title' => $godChild->getLastName() . ' ' . $godChild->getFirstName()]];
			usort($sponsorTypes, fn($a, $b) => $a['id'] == $sponsor->getTypeId() ? -1 : 1);
		}

		$this->render('editSponsor.twig', [
			'sponsor' => $sponsor,
			'people1' => $people,
			'people2' => $people2,
			'sponsorTypes' => $sponsorTypes
		]);
	}

}
