<?php

namespace App\controller;

use App\application\person\PersonService;
use App\infrastructure\router\Router;
use Twig\Environment;

class ErrorController extends Controller {

	public function __construct(Environment $twig, Router $router, PersonService $personService) {
		parent::__construct($twig, $router, $personService);
	}

	public function get(Router $router, array $parameters): void {

		$error = [
			'code' => 0,
			'message' => ''
		];

		switch ($router->getParameter('error')) {
			case 403:
				$error['code'] = 403;
				$error['message'] = 'accès refusé';
				break;
			case 404:
				$error['code'] = 404;
				$error['message'] = 'page non trouvée';
				break;
			default:
				header('Location: ' . $router->url('error', ['error' => 404]));
                die();
		}

		$this->render('error.twig', ['code' => $error['code'], 'message' => $error['message']]);
	}


}