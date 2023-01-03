<?php

namespace App\controller;

use App\infrastructure\router\Router;

class TreeController extends Controller
{

    public function get(Router $router, array $parameters): void
    {
        $people = $this->personService->getAllPeople();
        shuffle($people);
        $this->render('tree.twig', ['people' => $people]);
    }
}
