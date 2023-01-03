<?php

namespace App\controller;

use App\application\login\SignupService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;

class SignUpValidationController extends Controller
{
    private SignupService $signupService;

    public function __construct(
        Environment   $twig,
        Router        $router,
        PersonService $personService,
        SignupService $passwordService
    )
    {
        parent::__construct($twig, $router, $personService);
        $this->signupService = $passwordService;
    }

    public function get(Router $router, array $parameters): void
    {

        $error = $this->signupService->validate($parameters['token']);

        $this->render('signupValidation.twig', ['error' => $error]);
    }
}
