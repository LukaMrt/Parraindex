<?php

namespace App\controller;

use App\application\sponsor\SponsorService;
use App\infrastructure\router\Router;
use Twig\Environment;

class PersonController extends Controller {

	private SponsorService $sponsorService;

	public function __construct(Environment $twig, SponsorService $sponsorService) {
		parent::__construct($twig);
		$this->sponsorService = $sponsorService;
	}

	public function get(Router $router, array $parameters): void {

		$family = $this->sponsorService->getPersonFamily($parameters['id']);

		$this->render('person.twig', [
			'router' => $router,
			'person' => $family['person'],
			'godFathers' => $family['godFathers'],
			'godChildren' => $family['godChildren'],
			'characteristics' => $family['person']->getCharacteristics(),
			'admin' => ($_SESSION['privilege'] ?? 'STUDENT') === 'ADMIN'
		]);
	}

}