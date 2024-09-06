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
 * The reset password validation page, it's the page where the user can validate his password reset
 */
class ResetpasswordValidationController extends Controller
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
     * function get
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     * @throws LoaderError if the template is not found
     * @throws RuntimeError if the password is not valid
     * @throws SyntaxError if the password is not valid
     */
    public function get(Router $router, array $parameters): void
    {

        $error = $this->passwordService->validateResetPassword($parameters['token']);

        $this->render('resetpasswordValidation.twig', ['error' => $error]);
    }
}
