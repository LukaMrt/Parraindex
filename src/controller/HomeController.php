<?php

namespace App\controller;

use App\application\UserService;
use App\infrastructure\router\Router;
use Twig\Environment;

class HomeController extends Controller {

    private UserService $userService;

    public function __construct(Environment $twig, UserService $userService) {
        parent::__construct($twig);
        $this->userService = $userService;
    }

    public function get(Router $router, array $parameters): void {
        $this->render('home.html.twig');
    }

}
