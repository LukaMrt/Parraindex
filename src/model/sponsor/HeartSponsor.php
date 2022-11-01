<?php

namespace App\model\sponsor;

use App\model\person\Person;
use DateTime;

class HeartSponsor extends Sponsor {

	private string $description;

	public function __construct(int $id, Person $godFather, Person $godSon, DateTime $date, string $description) {
		parent::__construct($id, $godFather, $godSon, $date);
		$this->description = $description;
	}

	public function describe(): string {
		return $this->description;
	}

}