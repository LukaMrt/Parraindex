<?php

namespace App\controller;

use App\infrastructure\router\Router;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

abstract class Controller {

    private Environment $twig;

    public function __construct(Environment $twig) {
        $this->twig = $twig;
    }

    public function call(string $method, Router $router, array $parameters): void {

        switch ($method) {
            case 'GET':
                $this->get($router, $parameters);
                break;
            case 'POST':
                $this->post($router, $parameters);
                break;
            case 'PUT':
                $this->put($router, $parameters);
                break;
            case 'DELETE':
                $this->delete($router, $parameters);
                break;
        }

    }

    public function get(Router $router, array $parameters): void {
    }

    public function post(Router $router, array $parameters): void {
    }

    public function put(Router $router, array $parameters): void {
    }

    public function delete(Router $router, array $parameters): void {
    }

    protected function render(string $template, array $parameters = []): void {
        try {
            echo $this->twig->render($template, $parameters);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            if ($_ENV['DEBUG'] === "true") {
                dd($e);
            }
        }
    }

}