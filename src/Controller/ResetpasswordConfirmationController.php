<?php

namespace App\Controller;

use App\Infrastructure\old\router\Router;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * The reset password confirmation page, it's the page where the user can confirm his password reset
 */
class ResetpasswordConfirmationController extends Controller
{
    /**
     * @param Router $router the router
     * @param array $parameters the parameters
     * @return void
     * @throws LoaderError if the template cannot be found
     * @throws RuntimeError if an error occurred during the rendering
     * @throws SyntaxError if an error occurred during the rendering
     */
    public function get(Router $router, array $parameters): void
    {
        $this->render('resetpasswordConfirmation.html.twig');
    }
}
