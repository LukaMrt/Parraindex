<?php

namespace App\model\sponsor;

use App\model\person\Person;

class UnknownSponsor extends Sponsor {

	public function __construct($id_sponsor, Person $godFather, Person $godChild, string $date = '') {
		parent::__construct($id_sponsor, $godFather, $godChild, '');
	}

	public function getType(): string {
		return '';
	}

	public function getDescriptionTitle(): string {
		return '';
	}

	public function getDescription(): string {
		return '';
	}

	public function getIcon(): string {
		return 'interogation.svg';
	}

}