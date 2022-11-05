<?php

namespace App\model\incident;

enum IncidentType: int {

	case ADD = 0;
	case REMOVE = 1;
	case EDIT = 2;
	case ACCOUNT = 3;
	case OTHER = 4;

	public static function getValues(): array {
		return [
			['id' => 0, 'title' => 'Ajout'],
			['id' => 1, 'title' => 'Suppression'],
			['id' => 2, 'title' => 'Modification'],
			['id' => 3, 'title' => 'Compte'],
			['id' => 4, 'title' => 'Autre'],
		];
	}

}
