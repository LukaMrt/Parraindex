<?php

namespace App\controller;

use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * abstract class Controller
 * the controller is the link between the view and the domain
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
     * Controller constructor
     * @param Environment $twig the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     * initialize the controller
     */
    public function __construct(Environment $twig, Router $router, PersonService $personService)
    {
        $this->twig = $twig;
        $this->router = $router;
        $this->personService = $personService;
    }


    /**
     * function call
     * @param string $method the method to call
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     * look the method parameter to redirect to the right method
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
     * function get
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     * called when the HTTP method is GET
     */
    public function get(Router $router, array $parameters): void
    {
    }

    /**
     * function post
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     * called when the HTTP method is POST
     */
    public function post(Router $router, array $parameters): void
    {
    }


    /**
     * function put
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     * called when the HTTP method is PUT
     */
    public function put(Router $router, array $parameters): void
    {
    }


    /**
     * function delete
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     * called when the HTTP method is DELETE
     */
    public function delete(Router $router, array $parameters): void
    {
    }

    /**
     * function render
     * @param string $template the template
     * @param array $parameters the parameters
     * @return void
     * @throws LoaderError the loader error
     * @throws RuntimeError the runtime error
     * @throws SyntaxError the syntax error
     * render the twig template with the given parameters
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
