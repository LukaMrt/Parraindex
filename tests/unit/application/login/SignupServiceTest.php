<?php

namespace unit\application\login;

use App\application\login\AccountDAO;
use App\application\login\SignupService;
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

class SignupServiceTest extends TestCase {

	const DEFAULT_PARAMETERS = array(
		'firstname' => 'Test',
		'lastname' => 'testa',
		'email' => 'Test.testaaa@etu.univ-lyon1.fr',
		'password' => 'test',
		'password-confirm' => 'test'
	);
	private Account $validAccount;
	private Person $person;

	private SignupService $signupService;
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
		$this->signupService = new SignupService($this->accountDAO, $this->personDAO, $this->redirect, $this->mailer, $this->random, $this->urlUtils);
	}

	public function testSignupDetectsMissingFields(): void {

		$return = $this->signupService->signup(array());

		$this->assertEquals('Veuillez remplir tous les champs', $return);
	}

	public function testSignupDetectsInvalidEmail(): void {

		$parameters = self::DEFAULT_PARAMETERS;
		$parameters['email'] = 'test';

		$return = $this->signupService->signup($parameters);

		$this->assertEquals('L\'email doit doit être votre email universitaire', $return);
	}

	public function testSignupDetectsPasswordsMismatch(): void {

		$return = $this->signupService->signup(array(
			'lastname' => 'test',
			'firstname' => 'test',
			'email' => 'test.test@etu.univ-lyon1.fr',
			'password' => 'test',
			'password-confirm' => 'test2',
		));

		$this->assertEquals('Les mots de passe ne correspondent pas', $return);
	}

	public function testSignupDetectsUnknownName(): void {

		$return = $this->signupService->signup(self::DEFAULT_PARAMETERS);

		$this->assertEquals('Votre nom n\'est pas enregistré, merci de contacter un administrateur', $return);
	}

	public function testSignupDetectsAlreadyExistingAccountWithEmail(): void {

		$this->personDAO->method('getPerson')
			->with(new Identity('Test', 'testa'))
			->willReturn($this->person);

		$this->accountDAO->method("existsAccount")
			->willReturn(true);

		$return = $this->signupService->signup(self::DEFAULT_PARAMETERS);

		$this->assertEquals('Un compte existe déjà avec cette adresse email', $return);
	}

	public function testSignupDetectsAlreadyExistingAccountWithName(): void {

		$this->personDAO->method('getPerson')
			->with(new Identity('Test', 'testa'))
			->willReturn($this->person);

		$this->accountDAO->method("existsAccount")
			->willReturn(false);

		$this->accountDAO->method("existsAccountByIdentity")
			->willReturn(true);

		$return = $this->signupService->signup(self::DEFAULT_PARAMETERS);

		$this->assertEquals('Un compte existe déjà avec ce nom', $return);
	}

	public function testSignupDetectsEmailBelongingToSomeoneElse(): void {

		$this->personDAO->method('getPerson')
			->with(new Identity('Test', 'testa'))
			->willReturn($this->person);

		$this->personDAO->method('getAllIdentities')
			->willReturn(array(
				new Identity('teSTa', 'testb'),
				new Identity('testa', 'Test'),
				new Identity('azeazEZzeazeazeazeazeazeazeazeaz', 'aa'),
				new Identity('TeSt', 'tEst'),
			));

		$this->accountDAO->method("existsAccount")
			->willReturn(false);

		$this->accountDAO->method("existsAccountByIdentity")
			->willReturn(false);

		$params = self::DEFAULT_PARAMETERS;
		$params['email'] = 'tEsta.testb@etu.univ-lyon1.fr';
		$return = $this->signupService->signup($params);

		$this->assertEquals('D\'après notre recherche, cet email n\'est pas le vôtre', $return);
	}

	public function testSignupDetectsEmailTooFarFromName(): void {

		$this->personDAO->method('getPerson')
			->with(new Identity('Test', 'testa'))
			->willReturn($this->person);

		$this->personDAO->method('getAllIdentities')
			->willReturn(array());

		$this->accountDAO->method("existsAccount")
			->willReturn(false);

		$this->accountDAO->method("existsAccountByIdentity")
			->willReturn(false);

		$params = self::DEFAULT_PARAMETERS;
		$params['email'] = 'amkfjsqdmfkjqsdf.qsdùfkjqsdfkljsqdf@etu.univ-lyon1.fr';
		$return = $this->signupService->signup($params);

		$this->assertEquals('D\'après notre recherche, cet email n\'est pas le vôtre', $return);
	}

	public function testSignupRedirectToConfirmationPageOnSuccess(): void {

		$this->personDAO->method('getPerson')
			->with(new Identity('Test', 'testa'))
			->willReturn($this->person);

		$this->accountDAO->method("existsAccount")
			->willReturn(false);

		$this->accountDAO->method("existsAccountByIdentity")
			->willReturn(false);

		$this->personDAO->method('getAllIdentities')
			->willReturn(array(
				new Identity('Test', 'testa'),
				new Identity('azeazEZzeazeazeazeazeazeazeazeaz', 'aa'),
			));

		$this->redirect->expects($this->once())
			->method('redirect')
			->with('signup_confirmation');

		$this->signupService->signup(self::DEFAULT_PARAMETERS);
	}

	public function testSignupCreatesTemporaryAccountOnSuccess(): void {

		$this->personDAO->method('getPerson')
			->with(new Identity('Test', 'testa'))
			->willReturn($this->person);

		$this->accountDAO->method("existsAccount")
			->willReturn(false);

		$this->random->method('generate')
			->with(10)
			->willReturn('1');

		$this->accountDAO->expects($this->once())
			->method('createTemporaryAccount')
			->with(new Account(-1, 'test.testaaa@etu.univ-lyon1.fr', $this->person, new Password('test')), '1');

		$this->signupService->signup(self::DEFAULT_PARAMETERS);
	}

	public function testSignupSendsEmailOnSuccess(): void {

		$this->personDAO->method('getPerson')
			->with(new Identity('Test', 'testa'))
			->willReturn($this->person);

		$this->accountDAO->method("existsAccount")
			->willReturn(false);

		$this->random->method('generate')
			->with(10)
			->willReturn('1');

		$this->urlUtils->method('getBaseUrl')
			->willReturn('http://localhost');

		$this->urlUtils->method('buildUrl')
			->with('signup_validation', ['token' => '1'])
			->willReturn('/signup/validation/1');

		$this->mailer->expects($this->once())
			->method('send')
			->with('test.testaaa@etu.univ-lyon1.fr', 'Parraindex : inscription', "Bonjour Test testa,<br><br>Votre demande d'inscription a bien été enregistrée, merci de cliquer sur ce lien pour la valider : <a href=\"http://localhost/signup/validation/1\">http://localhost/signup/validation/1</a><br><br>Cordialement<br>Le Parrainboss");

		$this->signupService->signup(self::DEFAULT_PARAMETERS);
	}

	public function testValidateDetectsUnknownToken(): void {

		$this->accountDAO->method('getTemporaryAccountByToken')
			->with('1')
			->willReturn(new Account(-1, '', PersonBuilder::aPerson()->build(), new Password('')));

		$return = $this->signupService->validate('1');

		$this->assertEquals('Ce lien n\'est pas ou plus valide.', $return);
	}

	public function testValidateCreatesAccountOnSuccess(): void {

		$this->accountDAO->method('getTemporaryAccountByToken')
			->with('1')
			->willReturn($this->validAccount);

		$this->accountDAO->expects($this->once())
			->method('createAccount')
			->with($this->validAccount);

		$this->signupService->validate('1');
	}

	public function testValidateDeletesTemporaryAccountOnSuccess(): void {

		$this->accountDAO->method('getTemporaryAccountByToken')
			->with('1')
			->willReturn($this->validAccount);

		$this->accountDAO->expects($this->once())
			->method('deleteTemporaryAccount')
			->with($this->validAccount);

		$this->signupService->validate('1');
	}

	public function testResetpasswordDetectsUnknownEmail(): void {

		$this->accountDAO->method('existsAccount')
			->with('test.test@etu.univ-lyon1.fr')
			->willReturn(false);

		$return = $this->signupService->resetPassword(array('email' => 'test.test@etu.univ-lyon1.fr', 'password' => 'test'));

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

		$this->signupService->resetPassword(array('email' => 'test.test@etu.univ-lyon1.fr', 'password' => 'test'));
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

		$this->signupService->resetPassword(array('email' => 'test.test@etu.univ-lyon1.fr', 'password' => 'test'));
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

		$this->signupService->resetPassword(array('email' => 'test.test@etu.univ-lyon1.fr', 'password' => 'test'));
	}

	public function testValidateresetpasswordDetectsUnknownToken(): void {

		$this->accountDAO->method('getAccountResetPasswordByToken')
			->with('1')
			->willReturn(new Account(-1, '', PersonBuilder::aPerson()->build(), new Password('')));

		$return = $this->signupService->validateResetPassword('1');

		$this->assertEquals('Ce lien n\'est pas ou plus valide.', $return);
	}

	public function testValidateresetpasswordEditsAccountOnSuccess(): void {

		$this->accountDAO->method('getAccountResetPasswordByToken')
			->with('1')
			->willReturn($this->validAccount);

		$this->accountDAO->expects($this->once())
			->method('editAccountPassword')
			->with($this->validAccount);

		$this->signupService->validateResetPassword('1');
	}

	public function testValidateresetpasswordDeletesResetPasswordOnSuccess(): void {

		$this->accountDAO->method('getAccountResetPasswordByToken')
			->with('1')
			->willReturn($this->validAccount);

		$this->accountDAO->expects($this->once())
			->method('deleteResetPassword')
			->with($this->validAccount);

		$this->signupService->validateResetPassword('1');
	}

}
