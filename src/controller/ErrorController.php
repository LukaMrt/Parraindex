<?php

namespace App\controller;

use App\infrastructure\router\Router;
use Twig\Environment;

class ErrorController extends Controller {

	public function __construct(Environment $twig) {
		parent::__construct($twig);
	}

	public function get(Router $router, array $parameters): void {

		$error = [
			'code' => 0,
			'message' => ''
		];

		switch ($router->getParameter('error')) {
			case 403:
				$error['code'] = 403;
				$error['message'] = 'Accès refusé';
				break;
			case 404:
				$error['code'] = 404;
				$error['message'] = 'Page non trouvée';
				break;
			default:
				header('Location: ' . $router->url('error', ['error' => 404]));
				break;
		}

		$this->render('error.twig', ['code' => $error['code'], 'message' => $error['message']]);
	}


}