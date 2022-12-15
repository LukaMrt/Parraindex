<?php

namespace App\infrastructure\redirect;

use App\application\redirect\Redirect;
use App\infrastructure\router\Router;

class HttpRedirect implements Redirect {

	private Router $router;

	public function __construct(Router $router) {
		$this->router = $router;
	}

    public function redirect(string $url): void {
		header('Location: ' . $this->router->url($url));
	}

    public function delayedRedirect(string $url, int $secondsDelay): void {
		header('Refresh: ' . $secondsDelay . '; url=' . $this->router->url($url));
	}

}