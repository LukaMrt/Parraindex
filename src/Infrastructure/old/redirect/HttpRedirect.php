<?php

namespace App\Infrastructure\redirect;

use App\Application\redirect\Redirect;
use App\Infrastructure\router\Router;
use JetBrains\PhpStorm\NoReturn;

/**
 * Http redirection implementation of Redirect interface
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
     * @param string $url Url to redirect
     * @return void
     */
    #[NoReturn] public function redirect(string $url): void
    {
        header('Location: ' . $this->router->url($url));
        die();
    }


    /**
     * @param string $url Url to redirect
     * @param int $secondsDelay Delay in seconds
     * @return void
     */
    public function delayedRedirect(string $url, int $secondsDelay): void
    {
        header('Refresh: ' . $secondsDelay . '; url=' . $this->router->url($url));
    }
}
