<?php

namespace App\infrastructure\injector;

use App\controller\Controller;
use App\infrastructure\router\Router;
use App\model\incident\ContactType;
use Twig\Environment;

class ContactController extends Controller {

	public function __construct(Environment $twig) {
		parent::__construct($twig);
	}

	public function get(Router $router, array $parameters): void {
		$this->render('contact.twig', [
			'router' => $router,
			'options' => ContactType::getValues()
		]);
	}

}