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

	public function get(Router $router, array $parameters): void {

		$people = $this->personService->getAllPeople();
		$closure = fn($person) => ['id' => $person->getId(), 'title' => $person->getFirstName() . ' ' . $person->getLastName()];
		$people = array_map($closure, $people);

		$this->render('contact.twig', [
			'options' => ContactType::getValues(),
			'sponsorTypes' => [['id' => 0, 'title' => 'Parrainage IUT'], ['id' => 1, 'title' => 'Parrainage de coeur']],
			'people' => $people,
			'error' => $parameters['error'] ?? [],
		]);
	}

	public function post(Router $router, array $parameters): void {

		$error = $this->contactService->registerContact($_POST);

		$this->get($router, ['error' => $error]);
	}

}