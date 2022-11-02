<?php

namespace App\controller;

use App\infrastructure\router\Router;
use Twig\Environment;

class LoginController extends Controller {
	public function __construct(Environment $twig) {
		parent::__construct($twig);
	}

	public function get(Router $router, array $parameters): void {
		$this->render('login.twig');
	}
}