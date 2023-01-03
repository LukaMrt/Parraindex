<?php

namespace App\controller;

use App\infrastructure\router\Router;

/**
 * HomeController class
 * the home page, it's the first page of the website, this page redirect to other page
 */
class HomeController extends Controller
{
    public function __construct(Environment $twig, Router $router, PersonService $personService)
    {
        parent::__construct($twig, $router, $personService);
    }

    /**
     * function get
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     */
    public function get(Router $router, array $parameters): void
    {
        $this->render('home.twig', ['homePage' => true]);
    }

}
