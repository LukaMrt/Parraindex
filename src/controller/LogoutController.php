<?php

namespace App\controller;

use App\application\login\LoginService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use JetBrains\PhpStorm\NoReturn;
use Twig\Environment;

class LogoutController extends Controller
{
    private LoginService $loginService;

    public function __construct(
        Environment   $twig,
        Router        $router,
        PersonService $personService,
        LoginService  $loginService
    )
    {
        parent::__construct($twig, $router, $personService);
        $this->loginService = $loginService;
    }

    #[NoReturn] public function get(Router $router, array $parameters): void
    {
        $this->loginService->logout();
    }
}
