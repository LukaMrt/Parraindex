<?php

namespace App\controller;

use App\infrastructure\router\Router;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * The tree page, it's the page where the user can see all the persons
 */
class TreeController extends Controller
{

    /**
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     * @throws LoaderError if the template cannot be found
     * @throws RuntimeError if an error occurred during the rendering
     * @throws SyntaxError if an error occurred during the compilation
     */
    public function get(Router $router, array $parameters): void
    {
        $people = $this->personService->getAllPeople();
        shuffle($people);
        $this->render('tree.twig', ['people' => $people]);
    }

}
