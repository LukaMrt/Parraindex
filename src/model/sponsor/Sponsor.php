<?php

namespace App\model\sponsor;

use App\model\person\Person;
use DateTime;

abstract class Sponsor {

	private int $id;
	private Person $godFather;
	private Person $godSon;
	private DateTime $date;

	protected function __construct(int $id, Person $godFather, Person $godSon, DateTime $date) {
		$this->id = $id;
		$this->godFather = $godFather;
		$this->godSon = $godSon;
		$this->date = $date;
	}

	abstract public function describe(): string;

}