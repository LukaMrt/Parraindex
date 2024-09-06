<?php

namespace App\Controller;

use App\Application\login\SignupService;
use App\Application\person\PersonService;
use App\Infrastructure\router\Router;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * The signup page, it's the page where the user can sign up
 */
class SignUpController extends Controller
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
    public function __construct(
        Environment $twig,
        Router $router,
        PersonService $personService,
        SignupService $passwordService
    ) {
        parent::__construct($twig, $router, $personService);
        $this->signupService = $passwordService;
    }


    /**
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     * @throws LoaderError if the template is not found
     * @throws RuntimeError if an error occurs during the rendering
     * @throws SyntaxError if an error occurs during the rendering
     */
    public function get(Router $router, array $parameters): void
    {
        $this->render('signup.twig', ['router' => $router]);
    }


    /**
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     * @throws LoaderError if the template is not found
     * @throws RuntimeError if an error occurs during the rendering
     * @throws SyntaxError if an error occurs during the rendering
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
