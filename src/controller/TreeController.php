<?php

namespace App\controller;

use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;

class TreeController extends Controller
{
    public function __construct(Environment $twig, Router $router, PersonService $personService)
    {
        parent::__construct($twig, $router, $personService);
        $this->personService = $personService;
    }

    public function get(Router $router, array $parameters): void
    {
        $people = $this->personService->getAllPeople();
        shuffle($people);
        $this->render('tree.twig', ['people' => $people]);
    }
}
