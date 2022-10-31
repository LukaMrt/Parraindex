<?php

namespace App\controller;

use App\application\person\PersonDAO;
use App\infrastructure\router\Router;
use Twig\Environment;

class TreeController extends Controller {

    private PersonDAO $personDAO;

    public function __construct(Environment $twig, PersonDAO $personDAO) {
        parent::__construct($twig);
        $this->personDAO = $personDAO;
    }

    public function get(Router $router, array $parameters): void {
        $people = $this->personDAO->getAllPeople();
		$this->render('tree.twig', ['people' => $people]);
	}

}