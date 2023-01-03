<?php

namespace App\controller;

use App\infrastructure\router\Router;

class SignUpConfirmationController extends Controller
{
    public function get(Router $router, array $parameters): void
    {
        $this->render('signupConfirmation.twig');
    }
}
