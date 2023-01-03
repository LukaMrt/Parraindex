<?php

namespace App\controller;

use App\application\login\LoginService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;

class LoginController extends Controller
{
    private LoginService $loginService;

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

    public function get(Router $router, array $parameters): void
    {
        $this->render('login.twig', ['router' => $router]);
    }

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
