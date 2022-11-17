<?php

namespace App\controller;

use App\infrastructure\router\Router;
use Twig\Environment;

class ResetpasswordController extends Controller {
	public function get(Router $router, array $parameters): void {
		$this->render('resetpassword.twig', ['router' => $router]);
	}

}