<?php

namespace App\controller;

use App\application\contact\ContactService;
use App\application\person\PersonService;
use App\application\sponsor\SponsorService;
use App\infrastructure\router\Router;
use App\model\account\PrivilegeType;
use App\model\person\Identity;
use App\model\person\PersonBuilder;
use JetBrains\PhpStorm\NoReturn;
use Twig\Environment;

class ContactCloseController extends Controller {

	private ContactService $contactService;
	private SponsorService $sponsorService;

	public function __construct(Environment $twig, Router $router, PersonService $personService, ContactService $contactService, SponsorService $sponsorService) {
		parent::__construct($twig, $router, $personService);
		$this->contactService = $contactService;
		$this->sponsorService = $sponsorService;
	}

	#[NoReturn] public function get(Router $router, array $parameters): void {

		if (empty($_SESSION) || PrivilegeType::fromString($_SESSION['privilege']) !== PrivilegeType::ADMIN) {
			header('Location: ' . $router->url('error', ['error' => 403]));
			die();
		}

		if (!empty($parameters['resolve']) && $parameters['resolve'] === 'true') {
			$this->resolve($parameters['id']);
		}

		$contactId = $parameters['id'];
		$resolverId = $_SESSION['user']->getId();

		$this->contactService->closeContact($contactId, $resolverId);

		header('Location: ' . $router->url('contact_admin'));
		die();
	}

	private function resolve(int $id) {

		$contact = $this->contactService->getContact($id);

		switch ($contact->getTypeId()) {

			case 0:
				$person = PersonBuilder::aPerson()
					->withIdentity(new Identity($contact->getPerson()->getFirstName(), $contact->getPerson()->getLastName()))
					->withStartYear($contact->getPerson()->getStartYear())
					->build();
				$this->personService->addPerson($person);
				break;


			case 2:
				$this->personService->removePerson($contact->getPerson()->getId());
				break;

			case 3:
				$this->sponsorService->addSponsor($contact->getSponsor());
				break;

			case 5:
				$this->sponsorService->removeSponsor($contact->getSponsor()->getId());
				break;

			case 1:
			case 4:
			case 6:
			case 7:
			case 8:
			default:
				break;

		}

	}

}