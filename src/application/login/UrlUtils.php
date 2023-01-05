<?php

namespace App\application\login;

/**
 * Url utilities
 */
interface UrlUtils
{
    /**
     * Get the base url
     * @return string The base url
     */
    public function getBaseUrl(): string;


    /**
     * Build an url based on a route with the given parameters
     * @param string $route The route name
     * @param array $parameters The parameters to add to the url
     * @return string The url
     */
    public function buildUrl(string $route, array $parameters): string;
}
