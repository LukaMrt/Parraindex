<?php

namespace App\controller;

use App\infrastructure\router\Router;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * The logout confirmation page, it's the page where the user can confirm the logout
 */
class LogoutConfirmationController extends Controller
{
    /**
     * function get
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     * @throws LoaderError if the template is not found
     * @throws RuntimeError if an error occurs during the rendering
     * @throws SyntaxError if an error occurs during the rendering
     */
    public function get(Router $router, array $parameters): void
    {
        $this->render('logoutConfirmation.twig');
    }
}
