<?php

namespace App\model\person;

class Names {

	private string $firstName;
	private string $lastName;

	public function __construct(string $firstName, string $lastName) {
		$this->firstName = $firstName;
		$this->lastName = $lastName;
	}

	public static function default(): Names {
		return new Names("", "");
	}

	public function __toString(): string {
		return $this->firstName . ' ' . $this->lastName;
	}

	public function isEmpty(): bool {
		return empty($this->firstName) && empty($this->lastName);
	}

	public function getFirstName(): string {
		return $this->firstName;
	}

	public function getLastName(): string {
		return $this->lastName;
	}

}