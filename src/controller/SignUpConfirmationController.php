<?php

namespace App\controller;

use App\infrastructure\router\Router;

/**
 * class SignUpConfirmationController
 * the sign up confirmation page, it's the page where the user can confirm his sign up
 */
class SignUpConfirmationController extends Controller
{

    /**
     * function get
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     */
    public function get(Router $router, array $parameters): void
    {
        $this->render('signupConfirmation.twig');
    }

}
