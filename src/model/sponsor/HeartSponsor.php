<?php

namespace App\model\sponsor;

use App\model\person\Person;

class HeartSponsor extends Sponsor {

	private string $description;

	public function __construct(int $id, Person $godFather, Person $godSon, string $date, string $description) {
		parent::__construct($id, $godFather, $godSon, $date);
		$this->description = $description;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function getType(): string {
		return 'Parrainage de coeur';
	}

	public function getDescriptionTitle(): string {
		return 'Description';
	}

	public function getIcon(): string {
		return 'heart.svg';
	}

	public function getTypeId(): int {
		return 1;
	}

}