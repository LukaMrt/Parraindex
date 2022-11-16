<?php

namespace App\controller;

use App\application\login\LoginService;
use App\infrastructure\router\Router;
use Twig\Environment;

class SignUpController extends Controller {

	private LoginService $loginService;

	public function __construct(Environment $twig, LoginService $loginService) {
		parent::__construct($twig);
		$this->loginService = $loginService;
	}

    public function get(Router $router, array $parameters): void {
        $this->render('signup.twig', ['router' => $router]);
    }

    public function post(Router $router, array $parameters): void {

		$postParameters = [
			'firstname' => $_POST['firstname'] ?? '',
			'lastname' => $_POST['lastname'] ?? '',
			'email' => $_POST['email'] ?? '',
			'password' => $_POST['password'] ?? '',
			'password-confirm' => $_POST['password-confirm'] ?? ''
		];

		$error = $this->loginService->signUp($postParameters);

		$this->render('signup.twig', ['router' => $router, 'error' => $error]);
	}

}