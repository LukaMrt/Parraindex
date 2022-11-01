<?php

namespace App\model\family;

use App\model\person\Person;

class Family {

	private int $id;
	private string $name;
	private Person $creator;
	private array $members;

	public function __construct(int $id, string $name, Person $creator, Person... $members) {
		$this->id = $id;
		$this->name = $name;
		$this->creator = $creator;
		$this->members = $members;
	}

}