<?php

namespace App\infrastructure\login;

use App\application\login\UrlUtils;
use App\infrastructure\router\Router;

class DefaultUrlUtils implements UrlUtils
{

    private Router $router;


    public function __construct(Router $router)
    {
        $this->router = $router;
    }


    public function getBaseUrl(): string
    {

        $url = 'http';

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $url .= 's';
        }

        return $url . '://' . $_SERVER['HTTP_HOST'];
    }


    public function buildUrl(string $route, array $parameters): string
    {
        return $this->router->url($route, $parameters);
    }

}
