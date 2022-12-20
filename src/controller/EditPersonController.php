<?php

namespace App\controller;

use App\application\person\PersonService;
use App\application\person\characteristic\CharacteristicTypeService;
use App\application\person\characteristic\CharacteristicService;
use App\infrastructure\router\Router;
use App\model\account\PrivilegeType;
use Twig\Environment;

class EditPersonController extends Controller {

    private CharacteristicTypeService $characteristicTypeService;
    private CharacteristicService $characteristicService;

    public function __construct(Environment $twig, Router $router, PersonService $personService, CharacteristicTypeService $characteristicTypeService, CharacteristicService $characteristicService) {
        parent::__construct($twig, $router, $personService);
        $this->characteristicTypeService = $characteristicTypeService;
        $this->characteristicService = $characteristicService;
    }

    public function get(Router $router, array $parameters): void {

        $person = $this->personService->getPersonById($parameters['id']);

        // throw error if person does not exist
        if ($person === null) {
            header('Location: ' . $router->url('error', ['error' => 404]));
            die();
        }

        // throw error if user is not logged in
        if (empty($_SESSION)) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

        // throw error if user is not admin or the person to edit is not the user
        if ( PrivilegeType::fromString($_SESSION['privilege']) !== PrivilegeType::ADMIN &&
            $_SESSION['user']->getId() !== $person->getId()) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

        
        $characteristicTypes = $this->characteristicTypeService->getAllCharacteristicTypes();
        
        // Some code to test the services
        /* 
        $characteristic = $this->characteristicService->getCharacteristic(7,"Linkedin");
        dd($characteristicTypes, $characteristic);
        */

        $this->render('editPerson.twig', 
            [
            'person' => $person,
            'characteristics' => $characteristicTypes
            ]
        );
    }

	public function post(Router $router, array $parameters): void {

        $person = $this->personService->getPersonById($parameters['id']);

        // throw error if user is not logged in
        if (empty($_SESSION)) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

        // throw error if user is not admin or the person to edit is not the user
        if ( PrivilegeType::fromString($_SESSION['privilege']) !== PrivilegeType::ADMIN &&
            $_SESSION['user']->getId() !== $person->getId()) {
            header('Location: ' . $router->url('error', ['error' => 403]));
            die();
        }

		$data = [
			'id' => $parameters['id'],
			'first_name' => $_POST['firstName'],
			'last_name' => $_POST['lastName'],
			'biography' => $_POST['biography']
		];

		$this->personService->updatePerson($data);
		$person = $this->personService->getPersonById($parameters['id']);
		$this->render('editPerson.twig', ['success' => 'Modifications enregistrées', 'person' => $person]);
	}


}