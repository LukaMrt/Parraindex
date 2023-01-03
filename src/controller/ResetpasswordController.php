<?php

namespace App\controller;

use App\application\login\PasswordService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;

/**
 * class ResetpasswordController
 * the reset password page, it's the page where the user can reset his password
 */
class ResetpasswordController extends Controller
{

    /**
     * @var PasswordService the password service
     */
    private PasswordService $passwordService;


    /**
     * ResetpasswordController constructor
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
        $this->render('resetpassword.twig');
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
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
        ];

        $error = $this->passwordService->resetPassword($postParameters);

        $this->render('resetpassword.twig', ['error' => $error]);
    }

}
