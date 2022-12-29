<?php

namespace App\controller;

use App\application\login\SignupService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;

class ResetpasswordValidationController extends Controller {

	private SignupService $signupService;

	public function __construct(Environment $twig, Router $router, PersonService $personService, SignupService $signupService) {
		parent::__construct($twig, $router, $personService);
		$this->signupService = $signupService;
	}

	public function get(Router $router, array $parameters): void {

		$error = $this->signupService->validateResetPassword($parameters['token']);

		$this->render('resetpasswordValidation.twig', ['error' => $error]);
	}

}