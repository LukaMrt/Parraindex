<?php

namespace App\controller;

use App\infrastructure\router\Router;

/**
 * class TreeController
 * the tree page, it's the page where the user can see all the persons
 */
class TreeController extends Controller
{

    /**
     * function get
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     */
    public function get(Router $router, array $parameters): void
    {
        $people = $this->personService->getAllPeople();
        shuffle($people);
        $this->render('tree.twig', ['people' => $people]);
    }

}
