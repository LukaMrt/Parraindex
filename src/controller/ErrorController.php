<?php

namespace App\controller;

use App\infrastructure\router\Router;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * The error page, it's the page that explain the error
 */
class ErrorController extends Controller
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

        $error = [
            'code' => 0,
            'message' => ''
        ];

        switch ($router->getParameter('error')) {
            case 403:
                $error['code'] = 403;
                $error['message'] = 'accès refusé';
                break;
            case 404:
                $error['code'] = 404;
                $error['message'] = 'page non trouvée';
                break;
            case 500:
                $error['code'] = 500;
                $error['message'] = 'erreur serveur';
                break;
            default:
                header('Location: ' . $router->url('error', ['error' => 404]));
                die();
        }

        $this->render('error.twig', ['code' => $error['code'], 'message' => $error['message']]);
    }

}
