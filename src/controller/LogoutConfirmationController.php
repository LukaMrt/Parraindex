<?php

namespace App\controller;

use App\infrastructure\router\Router;

class LogoutConfirmationController extends Controller
{

    public function get(Router $router, array $parameters): void
    {
        $this->render('logoutConfirmation.twig');
    }
}
