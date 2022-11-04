<?php

namespace App\model\account;

use App\model\person\Person;

class Account {

	private int $id;
	private string $email;
	private Person $user;
	private array $privileges;

	public function __construct(int $id, string $email, Person $user, Privilege... $privileges) {
		$this->id = $id;
		$this->email = $email;
		$this->user = $user;
		$this->privileges = $privileges;
	}

}