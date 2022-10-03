<?php

namespace App\controller;

use App\infrastructure\router\Router;

abstract class Controller {

    public function get(Router $router, array $parameters = []): void {
    }

    public function post(Router $router, array $parameters = []): void {
    }

    public function put(Router $router, array $parameters = []): void {
    }

    public function delete(Router $router, array $parameters = []): void {
    }

}