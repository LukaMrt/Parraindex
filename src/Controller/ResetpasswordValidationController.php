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
 * The reset password validation page, it's the page where the user can validate his password reset
 */
class ResetpasswordValidationController extends Controller
{
    /**
     * @var PasswordService the password service
     */
    private PasswordService $passwordService;


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
        PasswordService $passwordService
    ) {
        parent::__construct($twigEnvironment, $router, $personService);
        $this->passwordService = $passwordService;
    }


    /**
     * function get
     * @param Router $router the router
     * @param array<string, string> $parameters the parameters
     * @throws LoaderError if the template is not found
     * @throws RuntimeError if the password is not valid
     * @throws SyntaxError if the password is not valid
     */
    #[\Override]
    public function get(Router $router, array $parameters): void
    {

        $error = $this->passwordService->validateResetPassword($parameters['token']);

        $this->render('resetpasswordValidation.html.twig', ['error' => $error]);
    }
}
