<?php

namespace App\controller;

use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * The controller is the link between the view and the domain. It handles the request and uses the appropriate service
 * to perform the requested actions and then returns the appropriate view.
 */
abstract class Controller
{
    /**
     * @var PersonService the person service
     */
    protected PersonService $personService;
    /**
     * @var Environment the twig environment
     */
    private Environment $twig;
    /**
     * @var Router the router
     */
    private Router $router;


    /**
     * @param Environment $twig the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     */
    public function __construct(Environment $twig, Router $router, PersonService $personService)
    {
        $this->twig = $twig;
        $this->router = $router;
        $this->personService = $personService;
    }


    /**
     * Looks the method parameter to redirect to the right method
     * @param string $method the method to call
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     */
    public function call(string $method, Router $router, array $parameters): void
    {

        switch ($method) {
            case 'POST':
                $this->post($router, $parameters);
                break;
            case 'PUT':
                $this->put($router, $parameters);
                break;
            case 'DELETE':
                $this->delete($router, $parameters);
                break;
            case 'GET':
            default:
                $this->get($router, $parameters);
        }
    }


    /**
     * Called when the HTTP method is POST
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     */
    public function post(Router $router, array $parameters): void
    {
    }


    /**
     * Called when the HTTP method is PUT
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     */
    public function put(Router $router, array $parameters): void
    {
    }


    /**
     * Called when the HTTP method is DELETE
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     */
    public function delete(Router $router, array $parameters): void
    {
    }


    /**
     * Called when the HTTP method is GET
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     */
    public function get(Router $router, array $parameters): void
    {
    }


    /**
     * Renders the twig template with the given parameters
     * @param string $template the template
     * @param array $parameters the parameters
     * @return void
     * @throws LoaderError if the template cannot be found
     * @throws RuntimeError if an error occurs during the rendering
     * @throws SyntaxError if an error occurs during the rendering
     */
    protected function render(string $template, array $parameters = []): void
    {

        if (!empty($_SESSION)) {
            $parameters['login'] = $_SESSION;
        }

        $parameters['router'] = $this->router;

        echo $this->twig->render($template, $parameters);
    }
}
