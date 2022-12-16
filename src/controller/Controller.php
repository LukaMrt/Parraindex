<?php

namespace App\controller;

use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;

abstract class Controller {

    private Environment $twig;
	private Router $router;
	protected PersonService $personService;

    public function __construct(Environment $twig, Router $router, PersonService $personService) {
        $this->twig = $twig;
		$this->router = $router;
		$this->personService = $personService;
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

		if (!empty($_SERVER['login'])) {
			$parameters['login'] = $this->personService->getPersonByLogin($_SERVER['login']);
		}

		$parameters['router'] = $this->router;

		echo $this->twig->render($template, $parameters);
    }

}