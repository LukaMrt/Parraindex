<?php

namespace App\model\contact;

class Contact {

	private string $name;
	private string $email;
	private ContactType $type;
	private string $description;

	public function __construct(string $name, string $email, ContactType $type, string $description) {
		$this->name = $name;
		$this->email = $email;
		$this->type = $type;
		$this->description = $description;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getEmail(): string {
		return $this->email;
	}

	public function getType(): string {
		return $this->type->toString();
	}

	public function getDescription(): string {
		return $this->description;
	}

}