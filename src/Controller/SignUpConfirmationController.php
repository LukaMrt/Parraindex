<?php

declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\old\router\Router;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * The signup confirmation page, it's the page where the user can confirm his signup
 */
class SignUpConfirmationController extends Controller
{
    /**
     * function get
     * @param Router $router the router
     * @param array $parameters the parameters
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    #[\Override]
    public function get(Router $router, array $parameters): void
    {
        $this->render('signupConfirmation.html.twig');
    }
}
