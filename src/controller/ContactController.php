<?php

namespace App\controller;

use App\application\contact\ContactService;
use App\infrastructure\router\Router;
use App\model\contact\ContactType;
use Twig\Environment;

class ContactController extends Controller {

	private ContactService $contactService;

	public function __construct(Environment $twig, ContactService $contactService) {
		parent::__construct($twig);
		$this->contactService = $contactService;
	}

	public function post(Router $router, array $parameters): void {

		$postParameters = [
			'firstname' => $_POST['firstname'],
			'lastname' => $_POST['lastname'],
			'email' => $_POST['email'],
			'type' => $_POST['type'],
			'description' => $_POST['description'],
		];

		$error = $this->contactService->registerContact($postParameters);

		$this->get($router, ['error' => $error]);
	}


	public function get(Router $router, array $parameters): void {

		$this->render('contact.twig', [
			'router' => $router,
			'options' => ContactType::getValues(),
			'error' => $parameters['error'] ?? ''
		]);
	}

}