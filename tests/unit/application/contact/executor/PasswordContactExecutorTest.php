<?php

namespace unit\application\contact\executor;

use App\application\contact\ContactDAO;
use App\application\contact\executor\ContactExecutor;
use App\application\contact\executor\PasswordContactExecutor;
use App\application\login\AccountDAO;
use App\application\login\UrlUtils;
use App\application\person\PersonDAO;
use App\application\random\Random;
use App\application\redirect\Redirect;
use App\model\account\Account;
use App\model\account\Password;
use App\model\contact\ContactType;
use App\model\contact\PersonContact;
use App\model\person\Identity;
use App\model\person\PersonBuilder;
use Monolog\Test\TestCase;

class PasswordContactExecutorTest extends TestCase
{
    private PersonDAO $personDAO;
    private AccountDAO $accountDAO;
    private Random $random;
    private UrlUtils $urlUtils;
    private ContactDAO $contactDAO;
    private ContactExecutor $executor;
    private Redirect $redirect;
    private const DEFAULT_PARAMS = [
        'senderFirstName' => 'test1',
        'senderLastName' => 'test2',
        'senderEmail' => 'test.test@test.com',
        'password' => 'test',
        'passwordConfirm' => 'test'
    ];


    public function setUp(): void
    {
        $this->personDAO = $this->createMock(PersonDAO::class);
        $this->accountDAO = $this->createMock(AccountDAO::class);
        $this->random = $this->createMock(Random::class);
        $this->urlUtils = $this->createMock(UrlUtils::class);
        $this->contactDAO = $this->createMock(ContactDAO::class);
        $this->redirect = $this->createMock(Redirect::class);

        $this->executor = new PasswordContactExecutor(
            $this->contactDAO,
            $this->redirect,
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

        $this->assertEquals('La personne doit exister', $result);
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