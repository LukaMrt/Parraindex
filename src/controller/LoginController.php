<?php

namespace App\controller;

use App\application\login\LoginService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;

/**
 * class LoginController
 * the login page, it's the page where the user can login
 */
class LoginController extends Controller
{

    /**
     * @var LoginService the login service
     */
    private LoginService $loginService;


    /**
     * LoginController constructor
     * @param Environment $twig the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param LoginService $passwordService the password service
     * initialize the controller
     */
    public function __construct(
        Environment   $twig,
        Router        $router,
        PersonService $personService,
        LoginService  $passwordService
    )
    {
        parent::__construct($twig, $router, $personService);
        $this->loginService = $passwordService;
    }


    /**
     * function get
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     */
    public function get(Router $router, array $parameters): void
    {
        $this->render('login.twig', ['router' => $router]);
    }


    /**
     * function post
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     */
    public function post(Router $router, array $parameters): void
    {

        $formParameters = [
            'login' => $_POST['login'] ?? '',
            'password' => $_POST['password'] ?? ''
        ];

        $error = $this->loginService->login($formParameters);

        $this->render('login.twig', ['error' => $error ?? '']);
    }

}
