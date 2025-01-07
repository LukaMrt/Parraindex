<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\person\PersonService;
use App\Infrastructure\old\router\Router;
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
     * @param Environment $twigEnvironment the twig environment
     * @param Router $router the router
     * @param PersonService $personService the person service
     */
    public function __construct(private readonly Environment $twigEnvironment, private readonly Router $router, protected PersonService $personService)
    {
    }


    /**
     * Looks the method parameter to redirect to the right method
     * @param string $method the method to call
     * @param Router $router the router
     * @param array<string, string> $parameters the parameters
     */
    public function call(string $method, Router $router, array $parameters): void
    {

        match ($method) {
            'POST' => $this->post($router, $parameters),
            'PUT' => $this->put($router, $parameters),
            'DELETE' => $this->delete($router, $parameters),
            default => $this->get($router, $parameters),
        };
    }


    /**
     * Called when the HTTP method is POST
     * @param Router $router the router
     * @param array<string, string> $parameters the parameters
     */
    public function post(Router $router, array $parameters): void
    {
    }


    /**
     * Called when the HTTP method is PUT
     * @param Router $router the router
     * @param array<string, string> $parameters the parameters
     */
    public function put(Router $router, array $parameters): void
    {
    }


    /**
     * Called when the HTTP method is DELETE
     * @param Router $router the router
     * @param array<string, string> $parameters the parameters
     */
    public function delete(Router $router, array $parameters): void
    {
    }


    /**
     * Called when the HTTP method is GET
     * @param Router $router the router
     * @param array<string, string> $parameters the parameters
     */
    public function get(Router $router, array $parameters): void
    {
    }


    /**
     * Renders the twig template with the given parameters
     * @param string $template the template
     * @param array<string, string> $parameters the parameters
     * @throws LoaderError if the template cannot be found
     * @throws RuntimeError if an error occurs during the rendering
     * @throws SyntaxError if an error occurs during the rendering
     */
    protected function render(string $template, array $parameters = []): void
    {

        if ($_SESSION !== []) {
            $parameters['login'] = $_SESSION;
        }

        $parameters['router'] = $this->router;

        echo $this->twigEnvironment->render($template, $parameters);
    }
}
