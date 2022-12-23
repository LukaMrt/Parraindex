<?php

namespace App\application\contact\executor;

use App\application\contact\ContactDAO;
use App\application\contact\field\EmailField;
use App\application\contact\field\Field;
use App\application\redirect\Redirect;
use App\model\contact\Contact;
use App\model\contact\ContactType;

class ChockingContentContactExecutor extends ContactExecutor {

	public function __construct(ContactDAO $contactDAO, Redirect $redirect) {

		parent::__construct($contactDAO, $redirect, ContactType::CHOCKING_CONTENT, [
			new Field('senderFirstName', 'Votre prénom doit contenir au moins 1 caractère'),
			new Field('senderLastName', 'Votre nom doit contenir au moins 1 caractère'),
			new EmailField('senderEmail', 'Votre email doit être valide'),
			new Field('message', 'La description doit contenir au moins 1 caractère'),
		]);

	}

	public function executeSuccess(array $data): string {
		$contact = new Contact(
			$data['senderFirstName'] . ' ' . $data['senderLastName'],
			$data['senderEmail'],
			ContactType::CHOCKING_CONTENT,
			$data['message'],
		);

		$this->contactDAO->saveSimpleContact($contact);
		return '';
	}

}