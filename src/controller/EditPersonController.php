<?php

namespace App\controller;

use App\application\person\PersonService;
use App\infrastructure\router\Router;
use App\model\account\PrivilegeType;
use Twig\Environment;

class EditPersonController extends Controller {

    public function __construct(Environment $twig, Router $router, PersonService $personService) {
        parent::__construct($twig, $router, $personService);
        $this->personService = $personService;
    }

    public function get(Router $router, array $parameters): void {

        if (PrivilegeType::fromString($_SESSION['privilege'] ?? 'STUDENT') !== PrivilegeType::ADMIN) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

        $person = $this->personService->getPersonById($parameters['id']);

        if ($person === null) {
            header('Location: ' . $router->url('error', ['error' => 404]));
            die();
        }

        $this->render('editPerson.twig', ['router' => $router, 'person' => $person]);
    }

	public function post(Router $router, array $parameters): void {

		if (PrivilegeType::fromString($_SESSION['privilege'] ?? 'STUDENT') !== PrivilegeType::ADMIN) {
			header('Location: ' . $router->url('error', ['error' => 403]));
			die();
		}

		$parameters = [
			'id' => $parameters['id'],
			'firstName' => $_POST['firstName'],
			'lastName' => $_POST['lastName'],
			'biography' => $_POST['biography']
		];

		$this->personService->updatePerson($parameters);
		header('Location: ' . $router->url('home'));
	}


}