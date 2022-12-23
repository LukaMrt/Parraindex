<?php

namespace App\model\person;
use DateTime;

class Identity {

	private string $firstName;
	private string $lastName;
	private ?string $picture;
	private ?DateTime $birthdate;

	public function __construct(string $firstName, string $lastName, ?string $picture = null, ?string $birthdate = null) {
		$this->firstName = $firstName;
		$this->lastName = $lastName;
		$this->picture = $picture ?? 'no-picture.svg';

		if ($birthdate) {
			$this->birthdate = DateTime::createFromFormat("Y-m-d", $birthdate);
		}
	}

	public static function default(): Identity {
		return new Identity("", "", null, null);
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

	public function getPicture(): string {
		return $this->picture;
	}

	public function setPicture(string $picture): void {
		$this->picture = $picture;
	}

}