<?php

namespace App\application\contact;

use App\application\contact\executor\ContactExecutors;

class ContactService {

	private ContactExecutors $contactExecutors;

	public function __construct(ContactExecutors $contactExecutors) {
		$this->contactExecutors = $contactExecutors;
	}

	public function registerContact(array $parameters): string {

		$id = -1;

		if (isset($parameters['type']) && is_numeric($parameters['type'])) {
			$id = $parameters['type'];
		}

		$executors = array_values($this->contactExecutors->getExecutorsById($id));

		if (count($executors) === 0) {
			return 'Le type de contact n\'est pas valide.';
		}

		return $executors[0]->execute($parameters);
	}

}
