<?php

namespace App\model\sponsor;

use App\model\person\Person;
use DateTime;

abstract class Sponsor {

	private int $id;
	private Person $godFather;
	private Person $godSon;
	private DateTime $date;

	protected function __construct(int $id, Person $godFather, Person $godSon, string $date) {
		$this->id = $id;
		$this->godFather = $godFather;
		$this->godSon = $godSon;

		if ($date) {
			$this->date = DateTime::createFromFormat("Y-m-d", $date);
		}
	}

	public function getId(): int {
		return $this->id;
	}

	public function getGodFather(): Person {
		return $this->godFather;
	}

	public function getGodSon(): Person {
		return $this->godSon;
	}

	public function getDate(): DateTime {
		return $this->date;
	}

	abstract public function getType(): string;

	abstract public function getDescriptionTitle(): string;

	abstract public function getDescription(): string;

	abstract public function getIcon(): string;

}