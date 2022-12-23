<?php

namespace unit\model\contact;

use App\model\contact\ContactType;
use Monolog\Test\TestCase;

class ContactTypeTest extends TestCase {

	public function testTostringReturnsCorrectString(): void {
		$contactType = ContactType::OTHER;
		$this->assertEquals('Other', $contactType->toString());
	}

	public function testGetvaluesReturnsCorrectArray(): void {
		$contactTypes = ContactType::getValues();
		$expected = [
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

		$this->assertEquals($expected, $contactTypes);
	}

}