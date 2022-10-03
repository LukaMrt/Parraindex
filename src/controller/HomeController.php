<?php

namespace App\controller;

use App\infrastructure\router\Router;

class HomeController extends Controller {

    public function get(Router $router, array $parameters = []): void {
        require VIEW_DIRECTORY . 'template.html';
    }

}
