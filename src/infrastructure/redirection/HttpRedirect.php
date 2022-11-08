<?php

namespace App\infrastructure\redirection;

use App\application\contact\Redirect;
use App\infrastructure\router\Router;
use JetBrains\PhpStorm\NoReturn;

class HttpRedirect implements Redirect {

	private Router $router;

	public function __construct(Router $router) {
		$this->router = $router;
	}

	#[NoReturn] public function redirect(string $url): void {
		header('Location: ' . $this->router->url($url));
		exit;
	}

}