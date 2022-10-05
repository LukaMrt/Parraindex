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

    public function put(string $url, Controller $controller, string $name): self {

        $closure = function (Router $router, array $parameters = []) use ($controller): void {
            $controller->put($router, $parameters);
        };

        return $this->registerRoute('PUT', $url, $name, $closure);
    }

    public function get(string $url, Controller $controller, string $name): self {

        $closure = function (Router $router, array $parameters = []) use ($controller): void {
            $controller->get($router, $parameters);
        };

        return $this->registerRoute('GET', $url, $name, $closure);
    }

    public function post(string $url, Controller $controller, string $name): self {

        $closure = function (Router $router, array $parameters = []) use ($controller): void {
            $controller->post($router, $parameters);
        };

        return $this->registerRoute('POST', $url, $name, $closure);
    }

    public function delete(string $url, Controller $controller, string $name): self {

        $closure = function (Router $router, array $parameters = []) use ($controller): void {
            $controller->delete($router, $parameters);
        };

        return $this->registerRoute('DELETE', $url, $name, $closure);
    }

    private function registerRoute(string $method, string $url, string $name, $closure): self {

        try {
            $this->router->map($method, $url, $closure, $name);
        } catch (Exception $e) {
            dump($e->getMessage());
        }

        return $this;
    }

    public function run(): void {
        $match = $this->router->match();
        $match['target']($this, $match['params']);
    }

}