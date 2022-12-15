<?php

namespace unit\application\contact;

use App\application\contact\ContactDAO;
use App\application\contact\ContactService;
use App\application\redirect\Redirect;
use App\model\contact\Contact;
use App\model\contact\ContactType;
use PHPUnit\Framework\TestCase;

class ContactServiceTest extends TestCase {

	const VALID_ARRAY = array(
		'firstname' => 'test',
		'lastname' => 'test',
		'email' => 'test@email.com',
		'type' => '7',
		'description' => 'testDescription'
	);

	private ContactService $contactService;
	private Redirect $redirect;
	private ContactDAO $contactDAO;

	public function setUp(): void {
		$this->contactDAO = $this->createMock(ContactDAO::class);
		$this->redirect = $this->createMock(Redirect::class);
		$this->contactService = new ContactService($this->contactDAO, $this->redirect);
	}

	public function testRegistercontactDetectsMissingFields(): void {

		$return = $this->contactService->registerContact(array());

		$this->assertEquals('Le prénom doit contenir au moins 1 caractère<br>'
			. 'Le nom doit contenir au moins 1 caractère<br>Le type doit être valide<br>'
			. 'L\'email doit être valide<br>La description doit contenir au moins 1 caractère', $return);
	}

	public function testRegistercontactDetectsInvalidFields(): void {

		$return = $this->contactService->registerContact(array(
			'firstname' => 'test',
			'lastname' => 'test',
			'email' => 'test',
			'type' => '0',
			'description' => ''
		));

		$this->assertEquals('L\'email doit être valide<br>La description doit contenir au moins 1 caractère', $return);
	}

	public function testRegistercontactSavesContact(): void {

		$contact = new Contact("test test", "test@email.com", ContactType::BUG, "testDescription");

		$this->contactDAO->expects($this->once())
			->method('saveContact')
			->with($contact);

		$this->contactService->registerContact(self::VALID_ARRAY);
	}

	public function testRegistercontactReturnsNothingOnSuccess(): void {

		$return = $this->contactService->registerContact(self::VALID_ARRAY);

		$this->assertEmpty($return);
	}

	public function testRegistercontactRedirectsToHomePageOnlyOnSuccess(): void {

		$this->redirect->expects($this->once())
			->method('redirect')
			->with('home');

		$this->contactService->registerContact(self::VALID_ARRAY);

		$this->contactService->registerContact(array());
	}

}
