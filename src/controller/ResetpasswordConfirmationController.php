<?php

namespace App\controller;

use App\infrastructure\router\Router;

class ResetpasswordConfirmationController extends Controller
{

    public function get(Router $router, array $parameters): void
    {
        $this->render('resetpasswordConfirmation.twig');
    }
}
