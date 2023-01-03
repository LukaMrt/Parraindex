<?php

namespace App\controller;

use App\application\login\SignupService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;

/**
 * class SignUpValidationController
 * the sign up validation page, it's the page where the user can validate his sign up
 */
class SignUpValidationController extends Controller
{

    /**
     * @var SignupService the signup service
     */
    private SignupService $signupService;


    /**
     * signUpValidationController constructor
     * @param Environment $twig the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param SignupService $passwordService the password service
     * initialize the controller
     */
    public function __construct(Environment $twig, Router $router, PersonService $personService, SignupService $passwordService)
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


    /**
     * function get
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     */
    public function get(Router $router, array $parameters): void
    {

        $error = $this->signupService->validate($parameters['token']);

        $this->render('signupValidation.twig', ['error' => $error]);
    }

}
