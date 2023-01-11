<?php

namespace App\controller;

use App\infrastructure\router\Router;

/**
 * Controller to download data
 */
class DataDownloadController extends Controller
{
	public function get(Router $router, array $parameters): void
	{
		if (empty($_SESSION)) {
			header('Location: ' . $router->url('error', ['error' => 403]));
			die();
		}

        header('Content-Type: application/json');

		$id = $_SESSION['user']->getId();
		echo $this->personService->getPersonData($id);
        die();
	}

}