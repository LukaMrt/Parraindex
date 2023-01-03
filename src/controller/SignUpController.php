<?php

namespace App\controller;

use App\application\login\SignupService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;

/**
 * class SignUpController
 * the sign up page, it's the page where the user can sign up
 */
class SignUpController extends Controller
{

    /**
     * @var SignupService the signup service
     */
    private SignupService $signupService;


    /**
     * SignUpController constructor
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
        $this->render('signup.twig', ['router' => $router]);
    }


    /**
     * function post
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     */
    public function post(Router $router, array $parameters): void
    {

        $postParameters = [
            'firstname' => $_POST['firstname'] ?? '',
            'lastname' => $_POST['lastname'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'password-confirm' => $_POST['password-confirm'] ?? ''
        ];

        $error = $this->signupService->signUp($postParameters);

        $this->render('signup.twig', ['error' => $error]);
    }

}
