<?php

namespace App\controller;

use App\application\Login;
use App\infrastructure\accountService\MysqlAccountDAO;
use App\application\AccountPassword;
use App\infrastructure\router\Router;
use Twig\Environment;

class LoginController extends Controller {

	public function __construct(Environment $twig) {
		parent::__construct($twig);
	}

	public function get(Router $router, array $parameters): void {
		$this->render('login.twig', ['router' => $router]);
	}

	public function post(Router $router, array $parameters): void {
		$login = new Login($router);
		$error = $login->login($router);
		$this->render('login.twig', ['router' => $router, 'error' => $error ?? '']);
	}

}