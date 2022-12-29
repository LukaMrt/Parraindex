<?php

namespace unit\application\contact;

use App\application\contact\ContactDAO;
use App\application\contact\ContactService;
use App\application\contact\executor\ContactExecutor;
use App\application\contact\executor\ContactExecutors;
use App\model\contact\Contact;
use App\model\contact\ContactType;
use App\model\contact\DefaultContact;
use PHPUnit\Framework\TestCase;

class ContactServiceTest extends TestCase {

	private ContactExecutors $contactExecutors;
	private ContactExecutor $contactExecutor;
	private ContactDAO $contactDAO;

	private ContactService $contactService;

	public function setUp(): void {
		$this->contactExecutors = $this->createMock(ContactExecutors::class);
		$this->contactExecutor = $this->createMock(ContactExecutor::class);
		$this->contactDAO = $this->createMock(ContactDAO::class);
		$this->contactService = new ContactService($this->contactExecutors, $this->contactDAO);
	}

	public function testRegistercontactReturnsErrorWhenTypeIsMissing(): void {
		$this->contactExecutors->method('getExecutorsById')
			->with(-1)
			->willReturn(array());

		$result = $this->contactService->registerContact(array());

		$this->assertEquals('Le type de contact n\'est pas valide.', $result);
	}

	public function testRegistercontactReturnsErrorWhenTypeIsNotValid(): void {

		$this->contactExecutors->method('getExecutorsById')
			->with(12345678)
			->willReturn(array());

		$result = $this->contactService->registerContact(array('type' => '12345678'));
		$this->assertEquals('Le type de contact n\'est pas valide.', $result);
	}

	public function testRegistercontactReturnsErrorWhenExecutorReturnsAnError(): void {

		$this->contactExecutors->method('getExecutorsById')
			->with(1)
			->willReturn([1 => $this->contactExecutor]);

		$this->contactExecutor->method('execute')
			->with(array('type' => '1'))
			->willReturn('Une erreur est survenue.');

		$result = $this->contactService->registerContact(array('type' => '1'));

		$this->assertEquals('Une erreur est survenue.', $result);
	}

	public function testRegistercontactReturnsNothingWhenExecutorReturnsNothing(): void {

		$this->contactExecutors->method('getExecutorsById')
			->with(1)
			->willReturn(array($this->contactExecutor));

		$this->contactExecutor->method('execute')
			->with(array('type' => '1'))
			->willReturn('');

		$result = $this->contactService->registerContact(array('type' => '1'));

		$this->assertEquals('', $result);
	}

	public function testClosecontactCallsClosecontactOnDAO(): void {

		$this->contactDAO->expects($this->once())
			->method('closeContact')
			->with(1, 1);

		$this->contactService->closeContact(1, 1);
	}

	public function testGetcontactReturnsWantendContact(): void {

		$contact = new DefaultContact(1, '', '', ContactType::OTHER, '');
		$contact2 = new DefaultContact(2, '', '', ContactType::OTHER, '');

		$this->contactDAO->method('getContactList')
			->willReturn([$contact, $contact2]);

		$result = $this->contactService->getContact(2);

		$this->assertEquals($contact2, $result);
	}

}
