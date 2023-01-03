<?php

namespace App\controller;

use App\application\login\PasswordService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;

class ResetpasswordValidationController extends Controller
{
    private PasswordService $passwordService;


    public function __construct(
        Environment $twig,
        Router $router,
        PersonService $personService,
        PasswordService $passwordService
    ) {
        parent::__construct($twig, $router, $personService);
        $this->passwordService = $passwordService;
    }


    public function get(Router $router, array $parameters): void
    {

        $error = $this->passwordService->validateResetPassword($parameters['token']);

        $this->render('resetpasswordValidation.twig', ['error' => $error]);
    }
}
