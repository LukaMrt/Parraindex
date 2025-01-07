<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\login\PasswordService;
use App\Application\person\PersonService;
use App\Infrastructure\old\router\Router;
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
     * @param Environment $twigEnvironment the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param PasswordService $passwordService the password service
     */
    public function __construct(
        Environment $twigEnvironment,
        Router $router,
        PersonService $personService,
        private readonly PasswordService $passwordService
    ) {
        parent::__construct($twigEnvironment, $router, $personService);
    }


    /**
     * @param Router $router the router
     * @param array<string, string> $parameters the parameters
     * @throws LoaderError if the template cannot be found
     * @throws RuntimeError if an error occurred during the rendering
     * @throws SyntaxError if an error occurred during the rendering
     */
    #[\Override]
    public function get(Router $router, array $parameters): void
    {
        $this->render('resetpassword.html.twig');
    }


    /**
     * @param Router $router the router
     * @param array<string, string> $parameters the parameters
     * @throws LoaderError if the template cannot be found
     * @throws RuntimeError if an error occurred during the rendering
     * @throws SyntaxError if an error occurred during the rendering
     */
    #[\Override]
    public function post(Router $router, array $parameters): void
    {

        $postParameters = [
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
        ];

        $error = $this->passwordService->resetPassword($postParameters);

        $this->render('resetpassword.html.twig', ['error' => $error]);
    }
}
