<?php

namespace App\controller;

use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;

class ResetpasswordConfirmationController extends Controller
{

    public function get(Router $router, array $parameters): void
    {
        $this->render('resetpasswordConfirmation.twig');
    }
}
