<?php

namespace application\contact;

use App\application\contact\ContactDAO;
use App\application\contact\ContactService;
use App\application\contact\Redirect;
use App\model\contact\Contact;
use App\model\contact\ContactType;
use PHPUnit\Framework\TestCase;

class ContactServiceTest extends TestCase {

	private ContactService $contactService;
	private Redirect $redirect;
	private ContactDAO $contactDAO;

	public function setUp(): void {
		$this->contactDAO = $this->createMock(ContactDAO::class);
		$this->redirect = $this->createMock(Redirect::class);
		$this->contactService = new ContactService($this->contactDAO, $this->redirect);
	}

	public function testRegistercontactDetectsMissingFields(): void {

		// Test 1
		$return = $this->contactService->registerContact(array());

		$this->assertEquals('Le prénom doit contenir au moins 1 caractère<br>Le nom doit contenir au moins 1 caractère<br>Le type doit être valide<br>L\'email doit être valide<br>La description doit contenir au moins 1 caractère', $return);

		// Test 2
		$return = $this->contactService->registerContact(array(
			'lastname' => 'test',
			'type' => '0'
		));

		$this->assertEquals('Le prénom doit contenir au moins 1 caractère<br>L\'email doit être valide<br>La description doit contenir au moins 1 caractère', $return);
	}

	public function testRegistercontactDetectsInvalidFields(): void {

		// Test 1
		$return = $this->contactService->registerContact(array(
			'firstname' => 'test',
			'lastname' => 'test',
			'email' => 'test',
			'type' => '0',
			'description' => ''
		));

		$this->assertEquals('L\'email doit être valide<br>La description doit contenir au moins 1 caractère', $return);

		// Test 2
		$return = $this->contactService->registerContact(array(
			'firstname' => 'test',
			'lastname' => 'test',
			'email' => 'test@test.com',
			'type' => '-1',
			'description' => 'test'
		));

		$this->assertEquals('Le type doit être valide', $return);
	}

	public function testRegistercontactSavesContact(): void {

		$contact = new Contact("test name", "test@email.com", ContactType::BUG, "testDescription");
		$contact2 = new Contact("test name2", "test2@email.com", ContactType::ADD_PERSON, "testDescription2");

		$this->contactDAO->expects($this->exactly(2))
			->method('saveContact')
			->withConsecutive([$contact], [$contact2]);

		$this->contactService->registerContact(array(
			'firstname' => 'test',
			'lastname' => 'name',
			'email' => 'test@email.com',
			'type' => '7',
			'description' => 'testDescription'
		));

		$this->contactService->registerContact(array(
			'firstname' => 'test',
			'lastname' => 'name2',
			'email' => 'test2@email.com',
			'type' => '0',
			'description' => 'testDescription2'
		));
	}

	public function testRegistercontactReturnsNothingOnSuccess(): void {
		$return = $this->contactService->registerContact(array(
			'firstname' => 'testName',
			'lastname' => 'testName',
			'email' => 'test@email.com',
			'type' => '7',
			'description' => 'testDescription'
		));

		$this->assertEquals('', $return);
	}

	public function testRegistercontactRedirectsToHomePageOnlyOnSuccess(): void {

		$this->redirect->expects($this->once())
			->method('redirect')
			->with('home');

		// Test 1
		$this->contactService->registerContact(array(
			'firstname' => 'testName',
			'lastname' => 'testName',
			'email' => 'test@email.com',
			'type' => '7',
			'description' => 'testDescription'
		));

		// Test 2
		$this->contactService->registerContact(array());
	}

}
