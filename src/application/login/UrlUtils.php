<?php

namespace App\application\login;

interface UrlUtils
{
    public function getBaseUrl(): string;

    public function buildUrl(string $route, array $parameters);
}
