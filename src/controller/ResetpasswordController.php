<?php

namespace App\controller;

use App\application\login\SignupService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;

class ResetpasswordController extends Controller {

	private SignupService $signupService;

	public function __construct(Environment $twig, Router $router, PersonService $personService, SignupService $signupService) {
		parent::__construct($twig, $router, $personService);
		$this->signupService = $signupService;
	}

	public function get(Router $router, array $parameters): void {
		$this->render('resetpassword.twig');
	}

	public function post(Router $router, array $parameters): void {

		$postParameters = [
			'email' => $_POST['email'] ?? '',
			'password' => $_POST['password'] ?? '',
		];

		$error = $this->signupService->resetPassword($postParameters);

		$this->render('resetpassword.twig', ['error' => $error]);
	}

}