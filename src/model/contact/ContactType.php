<?php

namespace App\model\contact;

enum ContactType: int {

	case ADD_PERSON = 0;
	case ADD_LINK = 1;
	case REMOVE_PERSON = 2;
	case REMOVE_LINK = 3;
	case UPDATE_PERSON = 4;
	case UPDATE_LINK = 5;
	case ACCOUNT = 6;
	case BUG = 7;
	case CHOCKING_CONTENT = 8;
	case OTHER = 9;

	public static function getValues(): array {
		return [
			['id' => 0, 'title' => 'Ajout d\'une personne'],
			['id' => 1, 'title' => 'Ajout d\'un lien'],
			['id' => 2, 'title' => 'Suppression d\'une personne'],
			['id' => 3, 'title' => 'Suppression d\'un lien'],
			['id' => 4, 'title' => 'Modification d\'une personne'],
			['id' => 5, 'title' => 'Modification d\'un lien'],
			['id' => 6, 'title' => 'Problème avec mon compte'],
			['id' => 7, 'title' => 'Bug'],
			['id' => 8, 'title' => 'Contenu choquant'],
			['id' => 9, 'title' => 'Autre'],
		];
	}

	public static function fromId(int $id): ?ContactType {
		return match ($id) {
			0 => self::ADD_PERSON,
			1 => self::ADD_LINK,
			2 => self::REMOVE_PERSON,
			3 => self::REMOVE_LINK,
			4 => self::UPDATE_PERSON,
			5 => self::UPDATE_LINK,
			6 => self::ACCOUNT,
			7 => self::BUG,
			8 => self::CHOCKING_CONTENT,
			9 => self::OTHER,
			default => null
		};
	}

}
