<?php

namespace unit\model\contact;

use App\model\contact\Contact;
use App\model\contact\ContactType;
use PHPUnit\Framework\TestCase;

class ContactTest extends TestCase {

	private Contact $contact;

	protected function setUp(): void {
		$this->contact = new Contact('John Doe', 'john.doe@etu.univ-lyon1.fr', ContactType::BUG, 'This is a contact message');
	}

	public function testGetnameReturnsName() {
		$this->assertEquals('John Doe', $this->contact->getName());
	}

	public function testGetemailReturnsEmail() {
		$this->assertEquals('john.doe@etu.univ-lyon1.fr', $this->contact->getEmail());
	}

	public function testGettypeReturnsContactType() {
		$this->assertEquals('Bug', $this->contact->getType());
	}

	public function testGetdescriptionReturnsMessage() {
		$this->assertEquals('This is a contact message', $this->contact->getDescription());
	}

}