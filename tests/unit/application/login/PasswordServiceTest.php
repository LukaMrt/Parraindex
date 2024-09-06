<?php

namespace App\Tests\unit\application\login;

use App\Application\logging\Logger;
use App\Application\login\AccountDAO;
use App\Application\login\PasswordService;
use App\Application\login\UrlUtils;
use App\Application\mail\Mailer;
use App\Application\person\PersonDAO;
use App\Application\random\Random;
use App\Application\redirect\Redirect;
use App\Entity\account\Account;
use App\Entity\account\Password;
use App\Entity\person\Identity;
use App\Entity\person\Person;
use App\Entity\person\PersonBuilder;
use PHPUnit\Framework\TestCase;

class PasswordServiceTest extends TestCase
{
    private const TEST_EMAIL = 'test.test@etu.univ-lyon1.fr';
    private Account $validAccount;
    private Person $person;

    private PasswordService $passwordService;
    private Redirect $redirect;
    private AccountDAO $accountDAO;
    private PersonDAO $personDAO;
    private Mailer $mailer;
    private Random $random;
    private UrlUtils $urlUtils;
    private Logger $logger;


    public function setUp(): void
    {

        $this->person = PersonBuilder::aPerson()
            ->withId(-1)
            ->withIdentity(new Identity('test', 'test'))
            ->build();

        $this->validAccount = new Account(1, self::TEST_EMAIL, $this->person, new Password('password'));

        $this->accountDAO = $this->createMock(AccountDAO::class);
        $this->personDAO = $this->createMock(PersonDAO::class);
        $this->redirect = $this->createMock(Redirect::class);
        $this->mailer = $this->createMock(Mailer::class);
        $this->random = $this->createMock(Random::class);
        $this->urlUtils = $this->createMock(UrlUtils::class);
        $this->logger = $this->createMock(Logger::class);
        $this->passwordService = new PasswordService(
            $this->accountDAO,
            $this->personDAO,
            $this->redirect,
            $this->mailer,
            $this->random,
            $this->urlUtils,
            $this->logger
        );
    }


    public function testResetpasswordDetectsUnknownEmail(): void
    {

        $this->accountDAO->method('existsAccount')
            ->with(self::TEST_EMAIL)
            ->willReturn(false);

        $this->logger->expects($this->once())
            ->method('error')
            ->with(PasswordService::class, 'Email not found');

        $return = $this->passwordService->resetPassword(['email' => self::TEST_EMAIL, 'password' => 'test']);

        $this->assertEquals('Email inconnu.', $return);
    }


    public function testResetpasswordRedirectsToConfirmationPageOnSuccess(): void
    {

        $this->accountDAO->method('existsAccount')
            ->with(self::TEST_EMAIL)
            ->willReturn(true);

        $this->redirect->expects($this->once())
            ->method('redirect')
            ->with('resetpassword_confirmation');

        $this->personDAO->method('getPersonById')
            ->with(-1)
            ->willReturn($this->person);

        $this->accountDAO->method('getAccountByLogin')
            ->with(self::TEST_EMAIL)
            ->willReturn($this->validAccount);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(PasswordService::class, 'Reset password email sent to ' . self::TEST_EMAIL);

        $this->passwordService->resetPassword(['email' => self::TEST_EMAIL, 'password' => 'test']);
    }


    public function testResetpasswordSendsEmailOnSuccess(): void
    {

        $this->accountDAO->method('existsAccount')
            ->with(self::TEST_EMAIL)
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
            ->with(self::TEST_EMAIL)
            ->willReturn($this->validAccount);

        $this->mailer->expects($this->once())
            ->method('send')
            ->with(
                self::TEST_EMAIL,
                'Parraindex : réinitialisation de mot de passe',
                "Bonjour Test Test,<br><br>Votre demande de réinitialisation de mot de passe "
                . "a bien été enregistrée, merci de cliquer sur ce lien pour la valider : "
                . "<a href=\"http://localhost/password/reset/1\">"
                . "http://localhost/password/reset/1</a><br><br>Cordialement<br>Le Parrainboss"
            );

        $this->passwordService->resetPassword(['email' => self::TEST_EMAIL, 'password' => 'test']);
    }


    public function testResetpasswordCreatesResetpasswordRecordOnSuccess(): void
    {

        $this->accountDAO->method('existsAccount')
            ->with(self::TEST_EMAIL)
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
            ->with(self::TEST_EMAIL)
            ->willReturn($this->validAccount);

        $account = new Account(1, self::TEST_EMAIL, $this->person, new Password('test'));

        $this->accountDAO->expects($this->once())
            ->method('createResetpassword')
            ->with($account, '1');

        $this->passwordService->resetPassword(['email' => self::TEST_EMAIL, 'password' => 'test']);
    }


    public function testValidateresetpasswordDetectsUnknownToken(): void
    {

        $this->accountDAO->method('getAccountResetPasswordByToken')
            ->with('1')
            ->willReturn(new Account(-1, '', PersonBuilder::aPerson()->build(), new Password('')));

        $this->logger->expects($this->once())
            ->method('error')
            ->with(PasswordService::class, 'Token invalid');

        $return = $this->passwordService->validateResetPassword('1');

        $this->assertEquals('Ce lien n\'est pas ou plus valide.', $return);
    }


    public function testValidateresetpasswordEditsAccountOnSuccess(): void
    {

        $this->accountDAO->method('getAccountResetPasswordByToken')
            ->with('1')
            ->willReturn($this->validAccount);

        $this->accountDAO->expects($this->once())
            ->method('editAccountPassword')
            ->with($this->validAccount);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(PasswordService::class, 'Password reset for ' . self::TEST_EMAIL);

        $this->passwordService->validateResetPassword('1');
    }


    public function testValidateresetpasswordDeletesResetPasswordOnSuccess(): void
    {

        $this->accountDAO->method('getAccountResetPasswordByToken')
            ->with('1')
            ->willReturn($this->validAccount);

        $this->accountDAO->expects($this->once())
            ->method('deleteResetPassword')
            ->with($this->validAccount);

        $this->passwordService->validateResetPassword('1');
    }
}
