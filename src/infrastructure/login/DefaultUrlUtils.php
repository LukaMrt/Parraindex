<?php

namespace App\infrastructure\login;

use App\application\login\UrlUtils;
use App\infrastructure\router\Router;

/**
 * Default url utils implementation. It uses the php global variables
 */
class DefaultUrlUtils implements UrlUtils
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
     * @return string The base url
     */
    public function getBaseUrl(): string
    {

        $url = 'http';

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $url .= 's';
        }

        return $url . '://' . $_SERVER['HTTP_HOST'];
    }


    /**
     * @param string $route Route name
     * @param array $parameters Parameters to add to the url
     * @return string The url
     */
    public function buildUrl(string $route, array $parameters): string
    {
        return $this->router->url($route, $parameters);
    }
}
