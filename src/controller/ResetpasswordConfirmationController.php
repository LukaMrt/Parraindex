<?php

namespace App\controller;

use App\infrastructure\router\Router;

/**
 * class ResetpasswordConfirmationController
 * the reset password confirmation page, it's the page where the user can confirm his password reset
 */
class ResetpasswordConfirmationController extends Controller
{

    /**
     * function get
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     */
    public function get(Router $router, array $parameters): void
    {
        $this->render('resetpasswordConfirmation.twig');
    }

}
