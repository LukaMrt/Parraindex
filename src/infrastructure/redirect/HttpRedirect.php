<?php

namespace App\infrastructure\redirect;

use App\application\redirect\Redirect;
use App\infrastructure\router\Router;
use JetBrains\PhpStorm\NoReturn;

/**
 * Redirect to a route
 */
class HttpRedirect implements Redirect
{
    /**
     * @var Router $router Router instance
     */
    private Router $router;


    /**
     * @param Router $router Router instance
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }


    /**
     * Redirect to a route
     * @param string $url Url to redirect
     * @return void
     */
    #[NoReturn] public function redirect(string $url): void
    {
        header('Location: ' . $this->router->url($url));
        die();
    }


    /**
     * Redirect to a route with a delay in seconds
     * @param string $url Url to redirect
     * @param int $secondsDelay Delay in seconds
     * @return void
     */
    public function delayedRedirect(string $url, int $secondsDelay): void
    {
        header('Refresh: ' . $secondsDelay . '; url=' . $this->router->url($url));
    }
}
