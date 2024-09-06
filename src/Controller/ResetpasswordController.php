<?php

namespace App\Controller;

use App\Application\login\PasswordService;
use App\Application\person\PersonService;
use App\Infrastructure\router\Router;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * The reset password page, it's the page where the user can reset his password
 */
class ResetpasswordController extends Controller
{
    /**
     * @var PasswordService the password service
     */
    private PasswordService $passwordService;


    /**
     * @param Environment $twig the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param PasswordService $passwordService the password service
     */
    public function __construct(
        Environment $twig,
        Router $router,
        PersonService $personService,
        PasswordService $passwordService
    ) {
        parent::__construct($twig, $router, $personService);
        $this->passwordService = $passwordService;
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
        $this->render('resetpassword.twig');
    }


    /**
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     * @throws LoaderError if the template cannot be found
     * @throws RuntimeError if an error occurred during the rendering
     * @throws SyntaxError if an error occurred during the rendering
     */
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
