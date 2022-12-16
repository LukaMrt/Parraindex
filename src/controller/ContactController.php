<?php

namespace App\controller;

use App\application\contact\ContactService;
use App\application\person\PersonService;
use App\infrastructure\router\Router;
use App\model\contact\ContactType;
use Twig\Environment;

class ContactController extends Controller {

	private ContactService $contactService;

	public function __construct(Environment $twig, Router $router, PersonService $personService, ContactService $contactService) {
		parent::__construct($twig, $router, $personService);
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
			'options' => ContactType::getValues(),
			'error' => $parameters['error'] ?? ''
		]);
	}

}