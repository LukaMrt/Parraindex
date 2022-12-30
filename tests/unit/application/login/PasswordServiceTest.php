<?php

namespace unit\application\login;

use App\application\login\AccountDAO;
use App\application\login\PasswordService;
use App\application\login\UrlUtils;
use App\application\mail\Mailer;
use App\application\person\PersonDAO;
use App\application\random\Random;
use App\application\redirect\Redirect;
use App\model\account\Account;
use App\model\account\Password;
use App\model\person\Identity;
use App\model\person\Person;
use App\model\person\PersonBuilder;
use PHPUnit\Framework\TestCase;

class PasswordServiceTest extends TestCase {

	const DEFAULT_PARAMETERS = array(
		'firstname' => 'Test',
		'lastname' => 'testa',
		'email' => 'Test.testaaa@etu.univ-lyon1.fr',
		'password' => 'test',
		'password-confirm' => 'test'
	);
	private Account $validAccount;
	private Person $person;

	private PasswordService $passwordService;
	private Redirect $redirect;
	private AccountDAO $accountDAO;
	private PersonDAO $personDAO;
	private Mailer $mailer;
	private Random $random;
	private UrlUtils $urlUtils;

	public function setUp(): void {

		$this->person = PersonBuilder::aPerson()
			->withId(-1)
			->withIdentity(new Identity('test', 'test'))
			->build();

		$this->validAccount = new Account(1, 'test.test@etu.univ-lyon1.fr', $this->person, new Password('password'));

		$this->accountDAO = $this->createMock(AccountDAO::class);
		$this->personDAO = $this->createMock(PersonDAO::class);
		$this->redirect = $this->createMock(Redirect::class);
		$this->mailer = $this->createMock(Mailer::class);
		$this->random = $this->createMock(Random::class);
		$this->urlUtils = $this->createMock(UrlUtils::class);
		$this->passwordService = new PasswordService($this->accountDAO, $this->personDAO, $this->redirect, $this->mailer, $this->random, $this->urlUtils);
	}

	public function testResetpasswordDetectsUnknownEmail(): void {

		$this->accountDAO->method('existsAccount')
			->with('test.test@etu.univ-lyon1.fr')
			->willReturn(false);

		$return = $this->passwordService->resetPassword(array('email' => 'test.test@etu.univ-lyon1.fr', 'password' => 'test'));

		$this->assertEquals('Email inconnu.', $return);
	}

	public function testResetpasswordRedirectsToConfirmationPageOnSuccess(): void {

		$this->accountDAO->method('existsAccount')
			->with('test.test@etu.univ-lyon1.fr')
			->willReturn(true);

		$this->redirect->expects($this->once())
			->method('redirect')
			->with('resetpassword_confirmation');

		$this->personDAO->method('getPersonById')
			->with(-1)
			->willReturn($this->person);

		$this->accountDAO->method('getAccountByLogin')
			->with('test.test@etu.univ-lyon1.fr')
			->willReturn($this->validAccount);

		$this->passwordService->resetPassword(array('email' => 'test.test@etu.univ-lyon1.fr', 'password' => 'test'));
	}

	public function testResetpasswordSendsEmailOnSuccess(): void {

		$this->accountDAO->method('existsAccount')
			->with('test.test@etu.univ-lyon1.fr')
			->willReturn(true);

		$this->urlUtils->method('getBaseUrl')
			->willReturn('http://localhost');

		$this->urlUtils->method('buildUrl')
			->with('resetpassword_validation', ['token' => '1'])
			->willReturn('/password/reset/1');

		$this->random->method('generate')
			->with(10)
			->willReturn('1');

		$this->personDAO->method('getPersonById')
			->with(-1)
			->willReturn($this->person);

		$this->accountDAO->method('getAccountByLogin')
			->with('test.test@etu.univ-lyon1.fr')
			->willReturn($this->validAccount);

		$this->mailer->expects($this->once())
			->method('send')
			->with('test.test@etu.univ-lyon1.fr', 'Parraindex : réinitialisation de mot de passe', "Bonjour test test,<br><br>Votre demande de réinitialisation de mot de passe a bien été enregistrée, merci de cliquer sur ce lien pour la valider : <a href=\"http://localhost/password/reset/1\">http://localhost/password/reset/1</a><br><br>Cordialement<br>Le Parrainboss");

		$this->passwordService->resetPassword(array('email' => 'test.test@etu.univ-lyon1.fr', 'password' => 'test'));
	}

	public function testResetpasswordCreatesResetpasswordRecordOnSuccess(): void {

		$this->accountDAO->method('existsAccount')
			->with('test.test@etu.univ-lyon1.fr')
			->willReturn(true);

		$this->urlUtils->method('getBaseUrl')
			->willReturn('http://localhost');

		$this->random->method('generate')
			->with(10)
			->willReturn('1');

		$this->personDAO->method('getPersonById')
			->with(-1)
			->willReturn($this->person);

		$this->accountDAO->method('getAccountByLogin')
			->with('test.test@etu.univ-lyon1.fr')
			->willReturn($this->validAccount);

		$account = new Account(1, 'test.test@etu.univ-lyon1.fr', $this->person, new Password('test'));

		$this->accountDAO->expects($this->once())
			->method('createResetpassword')
			->with($account, '1');

		$this->passwordService->resetPassword(array('email' => 'test.test@etu.univ-lyon1.fr', 'password' => 'test'));
	}

	public function testValidateresetpasswordDetectsUnknownToken(): void {

		$this->accountDAO->method('getAccountResetPasswordByToken')
			->with('1')
			->willReturn(new Account(-1, '', PersonBuilder::aPerson()->build(), new Password('')));

		$return = $this->passwordService->validateResetPassword('1');

		$this->assertEquals('Ce lien n\'est pas ou plus valide.', $return);
	}

	public function testValidateresetpasswordEditsAccountOnSuccess(): void {

		$this->accountDAO->method('getAccountResetPasswordByToken')
			->with('1')
			->willReturn($this->validAccount);

		$this->accountDAO->expects($this->once())
			->method('editAccountPassword')
			->with($this->validAccount);

		$this->passwordService->validateResetPassword('1');
	}

	public function testValidateresetpasswordDeletesResetPasswordOnSuccess(): void {

		$this->accountDAO->method('getAccountResetPasswordByToken')
			->with('1')
			->willReturn($this->validAccount);

		$this->accountDAO->expects($this->once())
			->method('deleteResetPassword')
			->with($this->validAccount);

		$this->passwordService->validateResetPassword('1');
	}

}
