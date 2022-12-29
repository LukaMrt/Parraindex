<?php

namespace App\model\contact;

// WARNING : Don't change the id of the values because it's related to the contact form

enum ContactType: int {

	case ADD_PERSON = 0;
	case UPDATE_PERSON = 1;
	case REMOVE_PERSON = 2;
	case ADD_SPONSOR = 3;
	case UPDATE_SPONSOR = 4;
	case REMOVE_SPONSOR = 5;
	case BUG = 6;
	case CHOCKING_CONTENT = 7;
	case OTHER = 8;

	public function toString(): string {
		return match ($this) {
			self::ADD_PERSON => "Ajout d'une personne",
			self::REMOVE_PERSON => "Suppression d'une personne",
			self::UPDATE_PERSON => "Modification d'une personne",
			self::ADD_SPONSOR => "Ajout d'un lien",
			self::REMOVE_SPONSOR => "Suppression d'un lien",
			self::UPDATE_SPONSOR => "Modification d'un lien",
			self::BUG => "Bug",
			self::CHOCKING_CONTENT => "Contenu choquant",
			self::OTHER => "Autre",
		};
	}

	public static function getValues(): array {
		return [
			['id' => 0, 'title' => 'Ajout d\'une personne'],
			['id' => 1, 'title' => 'Modification d\'une personne'],
			['id' => 2, 'title' => 'Suppression d\'une personne'],
			['id' => 3, 'title' => 'Ajout d\'un lien'],
			['id' => 4, 'title' => 'Modification d\'un lien'],
			['id' => 5, 'title' => 'Suppression d\'un lien'],
			['id' => 6, 'title' => 'Bug'],
			['id' => 7, 'title' => 'Contenu choquant'],
			['id' => 8, 'title' => 'Autre'],
		];
	}

	public static function fromId(int $id): ?ContactType {
		return match ($id) {
			0 => self::ADD_PERSON,
			1 => self::UPDATE_PERSON,
			2 => self::REMOVE_PERSON,
			3 => self::ADD_SPONSOR,
			4 => self::UPDATE_SPONSOR,
			5 => self::REMOVE_SPONSOR,
			6 => self::BUG,
			7 => self::CHOCKING_CONTENT,
			8 => self::OTHER,
			default => null
		};
	}

}
