<?php

namespace App\model\school;

use App\model\person\Person;
use DateTime;

class School {

	private int $id;
	private string $name;
	private SchoolAddress $address;
	private DateTime $creationDate;
	private ?Person $director;

	function __construct(int $id, string $name, SchoolAddress $address, DateTime $creationDate, ?Person $director) {
		$this->id = $id;
		$this->name = $name;
		$this->address = $address;
		$this->creationDate = $creationDate;
		$this->director = $director;
	}

}