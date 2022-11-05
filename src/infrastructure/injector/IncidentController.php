<?php

namespace App\infrastructure\injector;

use App\controller\Controller;
use App\infrastructure\router\Router;
use App\model\incident\IncidentType;
use Twig\Environment;

class IncidentController extends Controller {

	public function __construct(Environment $twig) {
		parent::__construct($twig);
	}

	public function get(Router $router, array $parameters): void {
		$this->render('incident.twig', [
			'router' => $router,
			'options' => IncidentType::getValues()
		]);
	}


}