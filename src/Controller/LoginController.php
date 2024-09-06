<?php

namespace App\Controller;

use App\Application\login\LoginService;
use App\Application\person\PersonService;
use App\Infrastructure\router\Router;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * The login page, it's the page where the user can log in
 */
class LoginController extends Controller
{
    /**
     * @var LoginService the login service
     */
    private LoginService $loginService;


    /**
     * @param Environment $twig the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param LoginService $passwordService the password service
     */
    public function __construct(
        Environment $twig,
        Router $router,
        PersonService $personService,
        LoginService $passwordService
    ) {
        parent::__construct($twig, $router, $personService);
        $this->loginService = $passwordService;
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
        $this->render('login.twig', ['router' => $router]);
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

        $formParameters = [
            'login' => $_POST['login'] ?? '',
            'password' => $_POST['password'] ?? ''
        ];

        $error = $this->loginService->login($formParameters);

        $this->render('login.twig', ['error' => $error ?? '']);
    }
}
