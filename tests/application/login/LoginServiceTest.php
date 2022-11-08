<?php

namespace application\login;

use App\application\login\AccountDAO;
use App\application\login\LoginService;
use App\application\login\SessionManager;
use App\application\person\PersonDAO;
use App\application\redirect\Redirect;
use App\model\account\Account;
use App\model\account\Password;
use App\model\person\Identity;
use App\model\person\PersonBuilder;
use PHPUnit\Framework\TestCase;

class LoginServiceTest extends TestCase {

	private LoginService $loginService;
	private Redirect $redirect;
	private AccountDAO $accountDAO;
	private PersonDAO $personDAO;
	private SessionManager $sessionManager;

	public function setUp(): void {
		$this->accountDAO = $this->createMock(AccountDAO::class);
		$this->personDAO = $this->createMock(PersonDAO::class);
		$this->redirect = $this->createMock(Redirect::class);
		$this->sessionManager = $this->createMock(SessionManager::class);
		$this->loginService = new LoginService($this->accountDAO, $this->personDAO, $this->redirect, $this->sessionManager);
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
			->with('test')
			->will($this->onConsecutiveCalls(new Password(password_hash('test', PASSWORD_DEFAULT)), new Password('')));

		$this->sessionManager->expects($this->once())
			->method('set')
			->with('login', 'test');

		$this->loginService->login(array(
			'login' => 'test',
			'password' => 'test',
		));

		$this->loginService->login(array(
			'login' => 'test',
			'password' => 'test',
		));
    }

	public function testLoginReturnsNothingOnSuccess(): void {

		$this->accountDAO->method('getAccountPassword')
			->with('test')
			->willReturn(new Password(password_hash('test', PASSWORD_DEFAULT)));

		$return = $this->loginService->login(array(
			'login' => 'test',
			'password' => 'test',
		));

		$this->assertEquals('', $return);
	}

	public function testLoginRedirectToHomeOnSuccess(): void {

		$this->accountDAO->method('getAccountPassword')
			->with('test')
			->will($this->onConsecutiveCalls(new Password(password_hash('test', PASSWORD_DEFAULT)), new Password('')));

		$this->redirect->expects($this->once())
			->method('redirect')
			->with('home');

		$this->loginService->login(array(
			'login' => 'test',
			'password' => 'test',
		));

		$this->loginService->login(array(
			'login' => 'test',
			'password' => 'test',
		));
	}

	public function testSignupDetectsMissingFields(): void {

		$return = $this->loginService->signup(array());

		$this->assertEquals('Veuillez remplir tous les champs', $return);
	}

	public function testSignupDetectsPasswordsMismatch(): void {

		$return = $this->loginService->signup(array(
			'lastname' => 'test',
			'firstname' => 'test',
			'email' => 'test',
			'password' => 'test',
			'password-confirm' => 'test2',
		));

		$this->assertEquals('Les mots de passe ne correspondent pas', $return);
	}

	public function testSignupDetectsInvalidNames(): void {

		$this->personDAO->method('getPerson')
			->with(new Identity('test', 'test'))
			->willReturn(null);

		$return = $this->loginService->signup(array(
			'lastname' => 'test',
			'firstname' => 'test',
			'email' => 'test',
			'password' => 'test',
			'password-confirm' => 'test'
		));

		$this->assertEquals('Votre nom n\'est pas enregistré, merci de contacter un administrateur', $return);
	}

	public function testSignupDetectsAlreadyExistingAccount(): void {

		$this->personDAO->method('getPerson')
			->with(new Identity('test', 'test'))
			->willReturn(PersonBuilder::aPerson()
				->withId(-1)
				->withIdentity(new Identity('test', 'test'))
				->build()
			);

		$this->accountDAO->method("existsAccount")
			->willReturn(true);

		$return = $this->loginService->signup(array(
			'lastname' => 'test',
			'firstname' => 'test',
			'email' => 'test',
			'password' => 'test',
			'password-confirm' => 'test'
		));

		$this->assertEquals('Un compte existe déjà avec cette adresse email', $return);
	}

	public function testSignupRedirectToHomePageOnSuccess(): void {

		$this->personDAO->method('getPerson')
			->with(new Identity('test', 'test'))
			->willReturn(PersonBuilder::aPerson()
				->withId(-1)
				->withIdentity(new Identity('test', 'test'))
				->build()
			);

		$this->accountDAO->method("existsAccount")
			->willReturn(false);

		$this->redirect->expects($this->once())
			->method('redirect')
			->with('home');

		$this->loginService->signup(array(
			'lastname' => 'test',
			'firstname' => 'test',
			'email' => 'test',
			'password' => 'test',
			'password-confirm' => 'test'
		));
	}

	public function testSignupCreatesAccountOnSuccess(): void {

		$person = PersonBuilder::aPerson()
			->withId(-1)
			->withIdentity(new Identity('test', 'test'))
			->build();

		$this->personDAO->method('getPerson')
			->with(new Identity('test', 'test'))
			->willReturn($person);

		$this->accountDAO->method("existsAccount")
			->willReturn(false);

		$this->accountDAO->expects($this->once())
			->method('createAccount')
			->with(new Account(-1, 'test', $person, new Password('test')));

		$this->loginService->signup(array(
			'lastname' => 'test',
			'firstname' => 'test',
			'email' => 'test',
			'password' => 'test',
			'password-confirm' => 'test'
		));
	}

}
