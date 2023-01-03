<?php

namespace App\controller;

use App\application\login\PasswordService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;

/**
 * class ResetpasswordValidationController
 * the reset password validation page, it's the page where the user can validate his password reset
 */
class ResetpasswordValidationController extends Controller
{

    /**
     * @var PasswordService the password service
     */
    private PasswordService $passwordService;


    /**
     * ResetpasswordValidationController constructor
     * @param Environment $twig the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * @param PasswordService $passwordService the password service
     * initialize the controller
     */
    public function __construct(
        Environment     $twig,
        Router          $router,
        PersonService   $personService,
        PasswordService $passwordService
    )
    {
        parent::__construct($twig, $router, $personService);
        $this->passwordService = $passwordService;
    }


    /**
     * function get
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     */
    public function get(Router $router, array $parameters): void
    {

        $error = $this->passwordService->validateResetPassword($parameters['token']);

        $this->render('resetpasswordValidation.twig', ['error' => $error]);
    }

}
