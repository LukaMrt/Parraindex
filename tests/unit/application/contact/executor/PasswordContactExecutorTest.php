<?php

namespace App\Tests\unit\application\contact\executor;

use App\Application\contact\ContactDAO;
use App\Application\contact\executor\ContactExecutor;
use App\Application\contact\executor\PasswordContactExecutor;
use App\Application\login\AccountDAO;
use App\Application\login\UrlUtils;
use App\Application\person\PersonDAO;
use App\Application\random\Random;
use App\Application\redirect\Redirect;
use App\Entity\account\Account;
use App\Entity\account\Password;
use App\Entity\contact\ContactType;
use App\Entity\contact\PersonContact;
use App\Entity\person\Identity;
use App\Entity\person\PersonBuilder;
use Monolog\Test\TestCase;

class PasswordContactExecutorTest extends TestCase
{
    private const DEFAULT_PARAMS = [
        'senderFirstName' => 'test1',
        'senderLastName' => 'test2',
        'senderEmail' => 'test.test@test.com',
        'password' => 'test',
        'passwordConfirm' => 'test'
    ];
    private PersonDAO $personDAO;
    private AccountDAO $accountDAO;
    private Random $random;
    private UrlUtils $urlUtils;
    private ContactDAO $contactDAO;
    private ContactExecutor $executor;


    public function setUp(): void
    {
        $this->personDAO = $this->createMock(PersonDAO::class);
        $this->accountDAO = $this->createMock(AccountDAO::class);
        $this->random = $this->createMock(Random::class);
        $this->urlUtils = $this->createMock(UrlUtils::class);
        $this->contactDAO = $this->createMock(ContactDAO::class);
        $redirect = $this->createMock(Redirect::class);

        $this->executor = new PasswordContactExecutor(
            $this->contactDAO,
            $redirect,
            $this->personDAO,
            $this->accountDAO,
            $this->random,
            $this->urlUtils
        );
    }


    public function testExecuteReturnsErrorWhenSenderFirstnameIsMissing()
    {

        $parameters = self::DEFAULT_PARAMS;
        $parameters['senderFirstName'] = '';

        $result = $this->executor->execute($parameters);

        $this->assertEquals('Votre prénom doit contenir au moins 1 caractère', $result);
    }


    public function testExecuteReturnsErrorWhenPasswordsAreNoteTheSame()
    {

        $parameters = self::DEFAULT_PARAMS;
        $parameters['passwordConfirm'] = 'test2';

        $result = $this->executor->execute($parameters);

        $this->assertEquals('Les mots de passe doivent être identiques', $result);
    }


    public function testExecuteReturnsErrorWhenPersonDoesNotExist()
    {

        $this->personDAO->method('getPerson')
            ->with(new Identity('test1', 'test2'))
            ->willReturn(null);

        $result = $this->executor->execute(self::DEFAULT_PARAMS);

        $expected = 'Cette carte n\'est pas enregistrée, veuillez faire une demande de création de personne avant';
        $this->assertEquals($expected, $result);
    }


    public function testExecuteReturnsErrorWhenEmailIsAlreadyUsed()
    {

        $person = PersonBuilder::aPerson()->withId(1)->build();

        $this->personDAO->method('getPerson')
            ->with(new Identity('test1', 'test2'))
            ->willReturn($person);

        $this->accountDAO->method('existsAccount')
            ->with(self::DEFAULT_PARAMS['senderEmail'])
            ->willReturn(true);

        $result = $this->executor->execute(self::DEFAULT_PARAMS);

        $expected = 'Cet email est déjà associée à un compte';
        $this->assertEquals($expected, $result);
    }


    public function testExecuteReturnsErrorWhenAccountIsAlreadyCreated()
    {

        $person = PersonBuilder::aPerson()->withId(1)->build();

        $identity = new Identity('test1', 'test2');

        $this->personDAO->method('getPerson')
            ->with($identity)
            ->willReturn($person);

        $this->accountDAO->method('existsAccount')
            ->with(self::DEFAULT_PARAMS['senderEmail'])
            ->willReturn(false);

        $this->accountDAO->method('existsAccountByIdentity')
            ->with($identity)
            ->willReturn(true);

        $result = $this->executor->execute(self::DEFAULT_PARAMS);

        $expected = 'Cette carte est déjà associée à un compte';
        $this->assertEquals($expected, $result);
    }


    public function testExecuteCreateTemporaryAccountOnSuccess()
    {

        $person = PersonBuilder::aPerson()->withId(1)->build();
        $account = new Account(
            1,
            self::DEFAULT_PARAMS['senderEmail'],
            $person,
            new Password(self::DEFAULT_PARAMS['password'])
        );

        $this->personDAO->method('getPerson')
            ->with(new Identity('test1', 'test2'))
            ->willReturn($person);

        $this->random->method('generate')
            ->with(10)
            ->willReturn('aaabbbcccd');

        $this->accountDAO->expects($this->once())
            ->method('createTemporaryAccount')
            ->with($account, 'aaabbbcccd');

        $this->executor->execute(self::DEFAULT_PARAMS);
    }


    public function testExecuteSavesContactOnSuccess()
    {

        $person = PersonBuilder::aPerson()->withId(1)->build();
        $contact = new PersonContact(
            -1,
            date('Y-m-d'),
            null,
            'test1 test2',
            self::DEFAULT_PARAMS['senderEmail'],
            ContactType::PASSWORD,
            'http://localhost/password/validation/aaabbbcccd',
            $person
        );

        $this->personDAO->method('getPerson')
            ->with(new Identity('test1', 'test2'))
            ->willReturn($person);

        $this->random->method('generate')
            ->with(10)
            ->willReturn('aaabbbcccd');

        $this->urlUtils->method('getBaseUrl')
            ->willReturn('http://localhost');

        $this->urlUtils->method('buildUrl')
            ->with('signup_validation', ['token' => 'aaabbbcccd'])
            ->willReturn('/password/validation/aaabbbcccd');

        $this->contactDAO->expects($this->once())
            ->method('savePersonUpdateContact')
            ->with($contact);

        $this->executor->execute(self::DEFAULT_PARAMS);
    }
}
