<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\login\SignupService;
use App\Application\person\PersonService;
use App\Infrastructure\old\router\Router;
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
     * @param Environment $twigEnvironment the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param SignupService $signupService the password service
     */
    public function __construct(
        Environment $twigEnvironment,
        Router $router,
        PersonService $personService,
        SignupService $signupService
    ) {
        parent::__construct($twigEnvironment, $router, $personService);
        $this->signupService = $signupService;
    }


    /**
     * @param Router $router the router
     * @param array $parameters the parameters
     * @throws LoaderError if the template is not found
     * @throws RuntimeError if an error occurs during the rendering
     * @throws SyntaxError if an error occurs during the rendering
     */
    #[\Override]
    public function get(Router $router, array $parameters): void
    {
        $this->render('signup.html.twig', ['router' => $router]);
    }


    /**
     * @param Router $router the router
     * @param array $parameters the parameters
     * @throws LoaderError if the template is not found
     * @throws RuntimeError if an error occurs during the rendering
     * @throws SyntaxError if an error occurs during the rendering
     */
    #[\Override]
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

        $this->render('signup.html.twig', ['error' => $error]);
    }
}
