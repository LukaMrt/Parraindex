<?php

namespace unit\model\contact;

use App\model\contact\ContactType;
use Monolog\Test\TestCase;

class ContactTypeTest extends TestCase {

	public function testTostringReturnsCorrectString(): void {
		$contactType = ContactType::ACCOUNT;
		$this->assertEquals('Account', $contactType->toString());
	}

	public function testGetvaluesReturnsCorrectArray(): void {
		$contactTypes = ContactType::getValues();
		$expected = [
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

		$this->assertEquals($expected, $contactTypes);
	}

}