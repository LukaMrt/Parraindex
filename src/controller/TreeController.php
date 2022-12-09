<?php

namespace App\controller;

use App\application\person\PersonDAO;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;

class TreeController extends Controller {

    private PersonService $personService;

    public function __construct(Environment $twig, PersonService $personService) {
        parent::__construct($twig);
        $this->personService = $personService;
    }

    public function get(Router $router, array $parameters): void {
        $people = $this->personService->getAllPeople();
		$this->render('tree.twig', [
			'router' => $router,
			'people' => $people
		]);
	}

}