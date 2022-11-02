<?php

namespace App\infrastructure\router;

use AltoRouter;
use App\controller\Controller;
use Exception;

class Router {

    private AltoRouter $router;

    public function __construct() {
        $this->router = new AltoRouter();
    }

    public function registerRoute(string $method, string $url, Controller $controller, string $name): self {

        $closure = function (Router $router, array $parameters = []) use ($controller, $method): void {
            $controller->call($method, $router, $parameters);
        };

        try {
            $this->router->map($method, $url, $closure, $name);
        } catch (Exception $e) {
            dd($e->getMessage());
        }

        return $this;
    }

    public function run(): void {
        $match = $this->router->match();
        $match['target']($this, $match['params']);
    }

}