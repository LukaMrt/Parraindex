<?php

namespace unit\application\contact\executor;

use App\application\contact\ContactDAO;
use App\application\contact\executor\AddPersonContactExecutor;
use App\application\person\PersonDAO;
use App\application\redirect\Redirect;
use App\model\contact\Contact;
use App\model\contact\ContactType;
use App\model\person\Identity;
use App\model\person\Person;
use App\model\person\PersonBuilder;
use PHPUnit\Framework\TestCase;

class AddPersonContactExecutorTest extends TestCase {

	private AddPersonContactExecutor $executor;

	private ContactDAO $contactDAO;
	private Redirect $redirect;
	private PersonDAO $personDAO;

	private array $defaultArray = [
		'senderFirstName' => 'test1',
		'senderLastName' => 'test2',
		'senderEmail' => 'test.test@test.com',
		'creationFirstName' => 'test3',
		'creationLastName' => 'test4',
		'entryYear' => 2022,
		'bonusInformation' => 'empty'
	];

	public function setUp(): void {

		$this->contactDAO = $this->createMock(ContactDAO::class);
		$this->redirect = $this->createMock(Redirect::class);
		$this->personDAO = $this->createMock(PersonDAO::class);

		$this->executor = new AddPersonContactExecutor($this->contactDAO, $this->redirect, $this->personDAO);
	}

	public function testExecuteReturnsErrorWhenSenderFirstnameIsMissing() {

		$this->defaultArray['senderFirstName'] = '';

		$result = $this->executor->execute($this->defaultArray);

		$this->assertEquals('Votre prénom doit contenir au moins 1 caractère', $result);
	}

	public function testExecuteSuccessReturnsErrorWhenPersonAlreadyExists(): void {

		$this->personDAO->method('getPerson')
			->with(new Identity('test3', 'test4'))
			->willReturn($this->createMock(Person::class));

		$result = $this->executor->execute($this->defaultArray);

		$this->assertEquals('La personne ne doit pas exister', $result);
	}

	public function testExecuteSuccessSavesContactWithGiveValues(): void {

		$this->personDAO->method('getPerson')
			->with(new Identity('test3', 'test4'))
			->willReturn(null);

		$person = PersonBuilder::aPerson()
			->withIdentity(new Identity('test3', 'test4'))
			->withStartYear(2022)
			->build();

		$contact = new Contact(
			'test1 test2',
			'test.test@test.com',
			ContactType::ADD_PERSON,
			'empty',
		);

		$this->contactDAO->expects($this->once())
			->method('savePersonAddContact')
			->with($person, $contact);

		$this->executor->execute($this->defaultArray);
	}

}