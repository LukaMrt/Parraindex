<?php

namespace App\application\contact;

use App\model\contact\Contact;
use App\model\contact\ContactType;

class ContactService {

	private ContactDAO $contactDAO;
	private Redirect $redirect;
	private array $fields;

	public function __construct(ContactDAO $contactDAO, Redirect $redirect) {

		$this->contactDAO = $contactDAO;
		$this->redirect = $redirect;
		$this->fields = [
			[
				'name' => 'name',
				'validation' => fn($value) => strlen($value) > 0,
				'error' => 'Le nom doit contenir au moins 1 caractère',
			],
			[
				'name' => 'type',
				'validation' => fn($value) => ContactType::fromId(intval($value)) !== null,
				'error' => 'Le type doit être valide',
			],
			[
				'name' => 'email',
				'validation' => fn($value) => filter_var($value, FILTER_VALIDATE_EMAIL),
				'error' => 'L\'email doit être valide',
			],
			[
				'name' => 'description',
				'validation' => fn($value) => strlen($value) > 0,
				'error' => 'La description doit contenir au moins 1 caractère',
			]
		];

	}


	public function registerContact(array $parameters): string {

		$parameters = [
			'name' => $parameters['name'] ?? '',
			'email' => $parameters['email'] ?? '',
			'type' => $parameters['type'] ?? '-1',
			'description' => $parameters['description'] ?? '',
		];

		$error = $this->buildErrorMessage($parameters);

		if ($error !== '') {
			return $error;
		}

		$contact = new Contact(
			$parameters['name'],
			$parameters['email'],
			ContactType::fromId(intval($parameters['type'])),
			$parameters['description']
		);

		$this->contactDAO->saveContact($contact);
		$this->redirect->redirect('home');
		return '';
	}

	public function buildErrorMessage(array $parameters): string {

		$invalidFields = array_filter($this->fields, function (array $field) use ($parameters) {
			return !$field['validation']($parameters[$field['name']]);
		});

		return implode('<br>', array_map(function (array $field) {
			return $field['error'];
		}, $invalidFields));
	}

}