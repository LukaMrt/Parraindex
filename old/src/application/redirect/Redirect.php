<?php

namespace App\application\redirect;

/**
 * Redirect to another page
 */
interface Redirect
{
    /**
     * Redirect to another page
     * @param string $url URL
     * @return void
     */
    public function redirect(string $url): void;


    /**
     * Redirect to another page with a delay in seconds
     * @param string $url URL
     * @param int $secondsDelay Seconds delay
     * @return void
     */
    public function delayedRedirect(string $url, int $secondsDelay): void;
}
