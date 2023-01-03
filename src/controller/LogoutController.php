<?php

namespace App\controller;

use App\application\login\LoginService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use JetBrains\PhpStorm\NoReturn;
use Twig\Environment;

/**
 * class LogoutController
 * the login page, it's the page where the user can logout
 */
class LogoutController extends Controller
{

    /**
     * @var LoginService the login service
     */
    private LoginService $loginService;


    /**
     * LogoutController constructor
     * @param Environment $twig the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param LoginService $loginService the login service
     * initialize the controller
     */
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


    /**
     * function get
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     */
    #[NoReturn] public function get(Router $router, array $parameters): void
    {
        $this->loginService->logout();
    }

}
