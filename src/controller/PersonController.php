<?php

namespace App\controller;

use App\application\person\PersonService;
use App\application\sponsor\SponsorService;
use App\infrastructure\router\Router;
use Twig\Environment;

class PersonController extends Controller {

	private PersonService $personService;
	private SponsorService $sponsorService;

	public function __construct(Environment $twig, PersonService $personService, SponsorService $sponsorService) {
		parent::__construct($twig);
		$this->personService = $personService;
		$this->sponsorService = $sponsorService;
	}

	public function get(Router $router, array $parameters): void {

		$person = $this->personService->getPersonById($parameters['id']);
		$family = $this->sponsorService->getFamily($parameters['id']);

		$this->render('person.twig', [
			'router' => $router,
			'person' => $person,
			'godFathers' => $family['godFathers'],
			'godChildren' => $family['godChildren'],
			'characteristics' => $person->getCharacteristics(),
			'admin' => $_SESSION['privilege'] ?? 'STUDENT' == 'ADMIN'
		]);
	}

}