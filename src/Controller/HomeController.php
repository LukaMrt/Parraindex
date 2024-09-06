<?php

namespace App\Controller;

use App\Infrastructure\router\Router;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * The home page, it's the first page of the website, this page redirect to other page
 */
class HomeController extends Controller
{
    /**
     * function get
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     * @throws LoaderError if the template is not found
     * @throws RuntimeError if an error occurred during the rendering
     * @throws SyntaxError if an error occurred during the rendering
     */
    public function get(Router $router, array $parameters): void
    {
        $this->render('home.twig', ['homePage' => true]);
    }
}
