<?php

namespace App\application\redirect;

interface Redirect
{
    public function redirect(string $url): void;


    public function delayedRedirect(string $url, int $secondsDelay): void;
}
