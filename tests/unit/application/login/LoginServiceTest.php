<?php

namespace unit\application\login;

use App\application\login\AccountDAO;
use App\application\login\LoginService;
use App\application\login\SessionManager;
use App\application\redirect\Redirect;
use App\model\account\Account;
use App\model\account\Password;
use App\model\account\Privilege;
use App\model\account\PrivilegeType;
use App\model\person\Identity;
use App\model\person\PersonBuilder;
use App\model\school\School;
use App\model\school\SchoolAddress;
use DateTime;
use PHPUnit\Framework\TestCase;

class LoginServiceTest extends TestCase {

	private Account $account;

	private LoginService $loginService;
	private Redirect $redirect;
	private AccountDAO $accountDAO;
	private SessionManager $sessionManager;

	public function setUp(): void {

		$person = PersonBuilder::aPerson()
			->withId(-1)
			->withIdentity(new Identity('test', 'test'))
			->build();
		$school = new School(0, 'school', new SchoolAddress('street', 'city'), new DateTime());
		$privilege = new Privilege($school, PrivilegeType::ADMIN);
		$this->account = new Account(0, 'test@test.com', $person, new Password('password'), $privilege);

		$this->accountDAO = $this->createMock(AccountDAO::class);
		$this->redirect = $this->createMock(Redirect::class);
		$this->sessionManager = $this->createMock(SessionManager::class);
		$this->loginService = new LoginService($this->accountDAO, $this->redirect, $this->sessionManager);
	}

	public function testLoginDetectsMissingFields(): void {

		$return = $this->loginService->login(array());

		$this->assertEquals('Veuillez remplir tous les champs', $return);
	}

	public function testLoginDetectsInvalidLogin(): void {

		$this->accountDAO->method('getAccountPassword')
			->with('test')
			->willReturn(new Password(''));

		$return = $this->loginService->login(array(
			'login' => 'test',
			'password' => 'test',
		));

		$this->assertEquals('Identifiant incorrect', $return);
	}

	public function testLoginSavesLoginInSessionOnSuccess(): void {

		$this->accountDAO->method('getAccountPassword')
			->with('test@test.com')
			->willReturn(new Password(password_hash('test', PASSWORD_DEFAULT)));

		$this->accountDAO->method('getSimpleAccount')
			->with('test@test.com')
			->willReturn($this->account);

		$this->sessionManager->expects($this->exactly(2))
			->method('set')
			->withConsecutive(
				['login', 'test@test.com'],
				['privilege', 'ADMIN']
			);

		$this->loginService->login(array(
			'login' => 'test@test.com',
			'password' => 'test',
		));
    }

	public function testLoginReturnsNothingOnSuccess(): void {

		$this->accountDAO->method('getAccountPassword')
			->with('test@test.com')
			->willReturn(new Password(password_hash('test', PASSWORD_DEFAULT)));

		$this->accountDAO->method('getSimpleAccount')
			->with('test@test.com')
			->willReturn($this->account);

		$return = $this->loginService->login(array(
			'login' => 'test@test.com',
			'password' => 'test',
		));

		$this->assertEmpty($return);
	}

	public function testLoginRedirectsToHomeOnSuccess(): void {

		$this->accountDAO->method('getAccountPassword')
			->with('test@test.com')
			->willReturn(new Password(password_hash('test', PASSWORD_DEFAULT)));

		$this->accountDAO->method('getSimpleAccount')
			->with('test@test.com')
			->willReturn($this->account);

		$this->redirect->expects($this->once())
			->method('redirect')
			->with('home');

		$this->loginService->login(array(
			'login' => 'test@test.com',
			'password' => 'test',
		));
	}

	public function testLoginRedirectToSignupIfNeeded(){

		$this->redirect->expects($this->once())
			->method('redirect')
			->with('signup_get');

		$this->loginService->login(array(
			'action' => 'register'
		));
	}

}
