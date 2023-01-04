<?php

namespace App\infrastructure\router;

use AltoRouter;
use App\controller\Controller;
use Exception;

/**
 * Router for the application
 */
class Router
{
    /**
     * @var AltoRouter $router Router instance
     */
    private AltoRouter $router;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->router = new AltoRouter();
    }


    /**
     * Register a route
     * @param string $method HTTP method
     * @param string $url Url
     * @param Controller $controller Controller instance
     * @param string $name Route name
     * @return $this
     */
    public function registerRoute(string $method, string $url, Controller $controller, string $name): self
    {

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


    /**
     * Run the router
     * @return void
     */
    public function run(): void
    {
        $match = $this->router->match();
        $match['target']($this, $match['params']);
    }


    /**
     * Get parameters from the url
     * @param string $name Parameter name
     * @return string
     */
    public function getParameter(string $name): string
    {

        $params = $this->router->match()['params'];

        if (isset($params[$name])) {
            return $params[$name];
        }

        return "";
    }


    /**
     * Get the url of a route
     * @param string $name Route name
     * @param array $parameters Parameters
     * @return string
     */
    public function url(string $name, array $parameters = []): string
    {
        try {
            return $this->router->generate($name, $parameters);
        } catch (Exception $e) {
            if ($_ENV['DEBUG'] === "true") {
                dd($e->getMessage());
            }
        }
        return '/';
    }
}
