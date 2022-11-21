<?php

namespace App\controller;

use App\application\person\PersonService;
use App\infrastructure\router\Router;
use App\model\account\PrivilegeType;
use Twig\Environment;

class EditPersonController extends Controller {

    private PersonService $personService;

    public function __construct(Environment $twig, PersonService $personService) {
        parent::__construct($twig);
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

}