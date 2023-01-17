<?php

namespace unit\application\login;

use App\application\logging\Logger;
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

class SignupServiceTest extends TestCase
{
    private const DEFAULT_PARAMETERS = [
        'firstname' => 'Test',
        'lastname' => 'testa',
        'email' => 'Test.testaaa@etu.univ-lyon1.fr',
        'password' => 'test',
        'password-confirm' => 'test'
    ];
    private Account $validAccount;
    private Person $person;

    private SignupService $signupService;
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

        $this->validAccount = new Account(1, 'test.test@etu.univ-lyon1.fr', $this->person, new Password('password'));

        $this->accountDAO = $this->createMock(AccountDAO::class);
        $this->personDAO = $this->createMock(PersonDAO::class);
        $this->redirect = $this->createMock(Redirect::class);
        $this->mailer = $this->createMock(Mailer::class);
        $this->random = $this->createMock(Random::class);
        $this->urlUtils = $this->createMock(UrlUtils::class);
        $this->logger = $this->createMock(Logger::class);
        $this->signupService = new SignupService(
            $this->accountDAO,
            $this->personDAO,
            $this->redirect,
            $this->mailer,
            $this->random,
            $this->urlUtils,
            $this->logger
        );
    }


    public function testSignupDetectsMissingFields(): void
    {

        $return = $this->signupService->signup([]);

        $this->assertEquals('Veuillez remplir tous les champs', $return);
    }


    public function testSignupDetectsInvalidEmail(): void
    {

        $parameters = self::DEFAULT_PARAMETERS;
        $parameters['email'] = 'test';

        $this->logger->expects($this->exactly(2))
            ->method('error')
            ->with(
                SignupService::class,
                'L\'email doit doit être votre email universitaire (' . implode(' ', $parameters) . ')'
            );

        $return = $this->signupService->signup($parameters);

        $this->assertEquals('L\'email doit doit être votre email universitaire', $return);
    }


    public function testSignupDetectsPasswordsMismatch(): void
    {

        $return = $this->signupService->signup([
            'lastname' => 'test',
            'firstname' => 'test',
            'email' => 'test.test@etu.univ-lyon1.fr',
            'password' => 'test',
            'password-confirm' => 'test2',
        ]);

        $this->assertEquals('Les mots de passe ne correspondent pas', $return);
    }


    public function testSignupDetectsUnknownName(): void
    {

        $return = $this->signupService->signup(self::DEFAULT_PARAMETERS);

        $expected = 'Votre nom n\'est pas enregistré, merci de faire une demande de création de personne';
        $this->assertEquals($expected, $return);
    }


    public function testSignupDetectsAlreadyExistingAccountWithEmail(): void
    {

        $this->personDAO->method('getPerson')
            ->with(new Identity('Test', 'testa'))
            ->willReturn($this->person);

        $this->accountDAO->method("existsAccount")
            ->willReturn(true);

        $return = $this->signupService->signup(self::DEFAULT_PARAMETERS);

        $this->assertEquals('Un compte existe déjà avec cette adresse email', $return);
    }


    public function testSignupDetectsAlreadyExistingAccountWithName(): void
    {

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


    public function testSignupDetectsEmailBelongingToSomeoneElse(): void
    {

        $this->personDAO->method('getPerson')
            ->with(new Identity('Test', 'testa'))
            ->willReturn($this->person);

        $this->personDAO->method('getAllIdentities')
            ->willReturn([
                new Identity('teSTa', 'testb'),
                new Identity('testa', 'Test'),
                new Identity('azeazEZzeazeazeazeazeazeazeazeaz', 'aa'),
                new Identity('TeSt', 'tEst'),
            ]);

        $this->accountDAO->method("existsAccount")
            ->willReturn(false);

        $this->accountDAO->method("existsAccountByIdentity")
            ->willReturn(false);

        $params = self::DEFAULT_PARAMETERS;
        $params['email'] = 'tEsta.testb@etu.univ-lyon1.fr';
        $return = $this->signupService->signup($params);

        $this->assertEquals('D\'après notre recherche, cet email n\'est pas le vôtre', $return);
    }


    public function testSignupDetectsEmailTooFarFromName(): void
    {

        $this->personDAO->method('getPerson')
            ->with(new Identity('Test', 'testa'))
            ->willReturn($this->person);

        $this->personDAO->method('getAllIdentities')
            ->willReturn([]);

        $this->accountDAO->method("existsAccount")
            ->willReturn(false);

        $this->accountDAO->method("existsAccountByIdentity")
            ->willReturn(false);

        $params = self::DEFAULT_PARAMETERS;
        $params['email'] = 'amkfjsqdmfkjqsdf.qsdùfkjqsdfkljsqdf@etu.univ-lyon1.fr';
        $return = $this->signupService->signup($params);

        $this->assertEquals('D\'après notre recherche, cet email n\'est pas le vôtre', $return);
    }


    public function testSignupRedirectToConfirmationPageOnSuccess(): void
    {

        $this->personDAO->method('getPerson')
            ->with(new Identity('Test', 'testa'))
            ->willReturn($this->person);

        $this->accountDAO->method("existsAccount")
            ->willReturn(false);

        $this->accountDAO->method("existsAccountByIdentity")
            ->willReturn(false);

        $this->personDAO->method('getAllIdentities')
            ->willReturn([
                new Identity('Test', 'testa'),
                new Identity('azeazEZzeazeazeazeazeazeazeazeaz', 'aa'),
            ]);

        $this->redirect->expects($this->once())
            ->method('redirect')
            ->with('signup_confirmation');

        $this->logger->expects($this->once())
            ->method('info')
            ->with(SignupService::class, 'Signup request sent to ' . strtolower(self::DEFAULT_PARAMETERS['email']));

        $this->signupService->signup(self::DEFAULT_PARAMETERS);
    }


    public function testSignupCreatesTemporaryAccountOnSuccess(): void
    {

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


    public function testSignupSendsEmailOnSuccess(): void
    {

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
            ->with(
                'test.testaaa@etu.univ-lyon1.fr',
                'Parraindex : inscription',
                "Bonjour Test testa,<br><br>Votre demande d'inscription a bien été enregistrée, merci de cliquer "
                . "sur ce lien pour la valider : <a href=\"http://localhost/signup/validation/1\">"
                . "http://localhost/signup/validation/1</a><br><br>Cordialement<br>Le Parrainboss"
            );

        $this->signupService->signup(self::DEFAULT_PARAMETERS);
    }


    public function testValidateDetectsUnknownToken(): void
    {

        $this->accountDAO->method('getTemporaryAccountByToken')
            ->with('1')
            ->willReturn(new Account(-1, '', PersonBuilder::aPerson()->build(), new Password('')));

        $this->logger->expects($this->once())
            ->method('error')
            ->with(SignupService::class, 'Token invalid');

        $return = $this->signupService->validate('1');

        $this->assertEquals('Ce lien n\'est pas ou plus valide.', $return);
    }


    public function testValidateCreatesAccountOnSuccess(): void
    {

        $this->accountDAO->method('getTemporaryAccountByToken')
            ->with('1')
            ->willReturn($this->validAccount);

        $this->accountDAO->expects($this->once())
            ->method('createAccount')
            ->with($this->validAccount);

        $this->signupService->validate('1');
    }


    public function testValidateDeletesTemporaryAccountOnSuccess(): void
    {

        $this->accountDAO->method('getTemporaryAccountByToken')
            ->with('1')
            ->willReturn($this->validAccount);

        $this->accountDAO->expects($this->once())
            ->method('deleteTemporaryAccount')
            ->with($this->validAccount);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(SignupService::class, 'Account created for test.test@etu.univ-lyon1.fr');

        $this->signupService->validate('1');
    }


    public function testValidateSensMailOnSuccess(): void
    {

        $this->accountDAO->method('getTemporaryAccountByToken')
            ->with('1')
            ->willReturn($this->validAccount);

        $this->mailer->expects($this->once())
            ->method('send');

        $this->signupService->validate('1');
    }
}
