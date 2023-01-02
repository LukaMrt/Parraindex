<?php

namespace unit\application\login;

use App\application\login\AccountDAO;
use App\application\login\LoginService;
use App\application\login\SessionManager;
use App\application\person\PersonDAO;
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

	const TEST_EMAIL = 'test@test.com';
	private Account $account;

	private LoginService $loginService;
	private Redirect $redirect;
	private AccountDAO $accountDAO;
	private PersonDAO $personDAO;
	private SessionManager $sessionManager;

	public function setUp(): void {

		$person = PersonBuilder::aPerson()
			->withId(-1)
			->withIdentity(new Identity('test', 'test'))
			->build();
		$school = new School(0, 'school', new SchoolAddress('street', 'city'), new DateTime());
		$privilege = new Privilege($school, PrivilegeType::ADMIN);
		$this->account = new Account(0, self::TEST_EMAIL, $person, new Password('password'), $privilege);

		$this->accountDAO = $this->createMock(AccountDAO::class);
		$this->personDAO = $this->createMock(PersonDAO::class);
		$this->redirect = $this->createMock(Redirect::class);
		$this->sessionManager = $this->createMock(SessionManager::class);
		$this->loginService = new LoginService(
			$this->accountDAO,
			$this->personDAO,
			$this->redirect,
			$this->sessionManager
		);
	}

	public function testLoginDetectsMissingFields(): void {

		$return = $this->loginService->login(array('login' => 'test'));

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
			->with(self::TEST_EMAIL)
			->willReturn(new Password(password_hash('test', PASSWORD_DEFAULT)));

		$this->accountDAO->method('getSimpleAccount')
			->with(self::TEST_EMAIL)
			->willReturn($this->account);

		$this->sessionManager->method('exists')
			->with('login')
			->willReturn(false);

		$person = PersonBuilder::aPerson()
			->withId(-1)
			->withIdentity(new Identity('test', 'test'))
			->build();

		$this->personDAO->method('getPersonByLogin')
			->with(self::TEST_EMAIL)
			->willReturn($person);

		$this->sessionManager->expects($this->exactly(3))
			->method('set')
			->withConsecutive(['login', self::TEST_EMAIL], ['privilege', 'ADMIN'], ['user', $person]);

		$this->loginService->login(array(
			'login' => self::TEST_EMAIL,
			'password' => 'test',
		));
	}

	public function testLoginReturnsNothingOnSuccess(): void {

		$this->accountDAO->method('getAccountPassword')
			->with(self::TEST_EMAIL)
			->willReturn(new Password(password_hash('test', PASSWORD_DEFAULT)));

		$this->accountDAO->method('getSimpleAccount')
			->with(self::TEST_EMAIL)
			->willReturn($this->account);

		$this->sessionManager->method('exists')
			->with('login')
			->willReturn(false);

		$return = $this->loginService->login(array(
			'login' => self::TEST_EMAIL,
			'password' => 'test',
		));

		$this->assertEmpty($return);
	}

	public function testLoginRedirectsToHomeOnSuccess(): void {

		$this->accountDAO->method('getAccountPassword')
			->with(self::TEST_EMAIL)
			->willReturn(new Password(password_hash('test', PASSWORD_DEFAULT)));

		$this->accountDAO->method('getSimpleAccount')
			->with(self::TEST_EMAIL)
			->willReturn($this->account);

		$this->redirect->expects($this->once())
			->method('redirect')
			->with('home');

		$this->sessionManager->method('exists')
			->with('login')
			->willReturn(false);

		$this->loginService->login(array(
			'login' => self::TEST_EMAIL,
			'password' => 'test',
		));
	}

	public function testLoginRedirectToSignupIfNeeded() {

		$this->redirect->expects($this->once())
			->method('redirect')
			->with('signup_get');

		$this->loginService->login(array(
			'action' => 'register'
		));
	}

	public function testLoginDetectsAlreadyLoggedIn(): void {

		$this->sessionManager->method('exists')
			->with('login')
			->willReturn(true);

		$return = $this->loginService->login(array(
			'login' => self::TEST_EMAIL,
			'password' => 'test',
		));

		$this->assertEquals('Vous êtes déjà connecté', $return);
	}

	public function testLogoutRedirectsToHomeWhenUserIsNotLogged(): void {

		$this->sessionManager->method('exists')
			->with('login')
			->willReturn(false);

		$this->redirect->expects($this->once())
			->method('redirect')
			->with('home');

		$this->loginService->logout();
	}

	public function testLogoutRedirectsToConfirmationWhenUserIsLogged(): void {

		$this->sessionManager->method('exists')
			->with('login')
			->willReturn(true);

		$this->redirect->expects($this->once())
			->method('redirect')
			->with('logout_confirmation');

		$this->loginService->logout();
	}

	public function testLogoutDestroysSessionsWhenUserIsLogged(): void {

		$this->sessionManager->method('exists')
			->with('login')
			->willReturn(true);

		$this->sessionManager->expects($this->exactly(1))
			->method('destroySession')
			->withConsecutive();

		$this->loginService->logout();
	}

}
