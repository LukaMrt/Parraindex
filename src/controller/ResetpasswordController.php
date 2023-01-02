<?php

namespace App\controller;

use App\application\login\PasswordService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;

class ResetpasswordController extends Controller
{
    private PasswordService $passwordService;

    public function __construct(Environment $twig, Router $router, PersonService $personService, PasswordService $passwordService)
    {
        parent::__construct($twig, $router, $personService);
        $this->passwordService = $passwordService;
    }

    public function get(Router $router, array $parameters): void
    {
        $this->render('resetpassword.twig');
    }

    public function post(Router $router, array $parameters): void
    {

        $postParameters = [
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
        ];

        $error = $this->passwordService->resetPassword($postParameters);

        $this->render('resetpassword.twig', ['error' => $error]);
    }
}
