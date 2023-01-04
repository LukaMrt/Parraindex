<?php

namespace App\infrastructure\login;

use App\application\login\UrlUtils;
use App\infrastructure\router\Router;

/**
 *
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
     * Get the url of the login page
     * @return string
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
     * Build the url of the login page
     * @param string $route Route name
     * @param array $parameters Parameters
     * @return string
     */
    public function buildUrl(string $route, array $parameters): string
    {
        return $this->router->url($route, $parameters);
    }
}
