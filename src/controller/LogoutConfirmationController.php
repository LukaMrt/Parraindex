<?php

namespace App\controller;

use App\infrastructure\router\Router;

/**
 * class LogoutConfirmationController
 * the logout confirmation page, it's the page where the user can confirm the logout
 */
class LogoutConfirmationController extends Controller
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
        $this->render('logoutConfirmation.twig');
    }

}
