<?php

namespace App\controller;

use App\application\login\SignupService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * The signup validation page, it's the page where the user can validate his signup
 */
class SignUpValidationController extends Controller
{

    /**
     * @var SignupService the signup service
     */
    private SignupService $signupService;


    /**
     * @param Environment $twig the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param SignupService $passwordService the password service
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
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     * @throws LoaderError if the template cannot be found
     * @throws RuntimeError if an error occurred during the rendering
     * @throws SyntaxError if an error occurred during the rendering
     */
    public function get(Router $router, array $parameters): void
    {

        $error = $this->signupService->validate($parameters['token']);

        $this->render('signupValidation.twig', ['error' => $error]);
    }

}
