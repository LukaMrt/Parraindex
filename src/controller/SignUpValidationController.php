<?php

namespace App\controller;

use App\application\login\SignupService;
use App\infrastructure\router\Router;
use Twig\Environment;

class SignUpValidationController extends Controller {

	private SignupService $signupService;

	public function __construct(Environment $twig, SignupService $signupService) {
		parent::__construct($twig);
		$this->signupService = $signupService;
	}

	public function get(Router $router, array $parameters): void {

		$error = $this->signupService->validate($parameters['token']);

		$this->render('signupValidation.twig', ['router' => $router, 'error' => $error]);
	}


}