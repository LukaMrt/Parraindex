<?php

namespace App\application\contact\executor;

use App\application\contact\ContactDAO;
use App\application\contact\field\YearField;
use App\application\contact\field\EmailField;
use App\application\contact\field\Field;
use App\application\person\PersonDAO;
use App\application\redirect\Redirect;
use App\model\contact\Contact;
use App\model\contact\ContactType;
use App\model\person\Identity;
use App\model\person\PersonBuilder;

class AddPersonContactExecutor extends ContactExecutor {

	private PersonDAO $personDAO;

	public function __construct(ContactDAO $contactDAO, Redirect $redirect, PersonDAO $personDAO) {

		parent::__construct($contactDAO, $redirect, ContactType::ADD_PERSON, [
			new Field('senderFirstName', 'Votre prénom doit contenir au moins 1 caractère'),
			new Field('senderLastName', 'Votre nom doit contenir au moins 1 caractère'),
			new EmailField('senderEmail', 'Votre email doit être valide'),
			new Field('creationFirstName', 'Le prénom doit contenir au moins 1 caractère'),
			new Field('creationLastName', 'Le nom doit contenir au moins 1 caractère'),
			new YearField('entryYear', 'L\'année doit être valide'),
		]);
		$this->personDAO = $personDAO;
	}

	public function executeSuccess(array $data): string {

		if ($this->personDAO->getPerson(new Identity($data['creationFirstName'], $data['creationLastName'])) !== null) {
			return 'La personne ne doit pas exister';
		}

		$person = PersonBuilder::aPerson()
			->withIdentity(new Identity($data['creationFirstName'], $data['creationLastName']))
			->withStartYear($data['entryYear'])
			->build();

		$contact = new Contact(
			$data['senderFirstName'] . ' ' . $data['senderLastName'],
			$data['senderEmail'],
			ContactType::ADD_PERSON,
			$data['bonusInformation'] ?? '',
		);

		$this->contactDAO->savePersonAddContact($person, $contact);
		return '';
	}

}