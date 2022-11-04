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

	public function getParameter(string $name): string {

		$params = $this->router->match()['params'];

		if (isset($params[$name])) {
			return $params[$name];
		}

		return "";
	}

	public function url(string $name, array $parameters = []): string {
		try {
			return $this->router->generate($name, $parameters);
		} catch (Exception $e) {
			if ($_ENV['DEBUG']) {
				dd($e->getMessage());
			}
		}
		return '/';
	}

}