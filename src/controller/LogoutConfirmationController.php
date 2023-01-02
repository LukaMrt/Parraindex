<?php

namespace App\controller;

use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;

class LogoutConfirmationController extends Controller
{
    public function __construct(Environment $twig, Router $router, PersonService $personService)
    {
        parent::__construct($twig, $router, $personService);
    }

    public function get(Router $router, array $parameters): void
    {
        $this->render('logoutConfirmation.twig');
    }
}
