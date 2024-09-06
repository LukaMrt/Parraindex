<?php

namespace App\Application\login;

use App\Application\logging\Logger;
use App\Application\mail\Mailer;
use App\Application\person\PersonDAO;
use App\Application\random\Random;
use App\Application\redirect\Redirect;
use App\Entity\account\Account;
use App\Entity\account\Password;
use App\Entity\person\Identity;
use App\Entity\person\Person;

/**
 * Service for signing up new users
 */
class SignupService
{
    /**
     * @var string regex a non letter character
     */
    private const NON_LETTER_REGEX = "/[^a-z]/";
    /**
     * @var AccountDAO DAO for accounts
     */
    private AccountDAO $accountDAO;
    /**
     * @var PersonDAO DAO for persons
     */
    private PersonDAO $personDAO;
    /**
     * @var Redirect redirect service
     */
    private Redirect $redirect;
    /**
     * @var Mailer mailer service
     */
    private Mailer $mailer;
    /**
     * @var Random random generator
     */
    private Random $random;
    /**
     * @var UrlUtils Url utilities
     */
    private UrlUtils $urlUtils;
    /**
     * @var Logger logger
     */
    private Logger $logger;


    /**
     * @param AccountDAO $accountDAO DAO for accounts
     * @param PersonDAO $personDAO DAO for persons
     * @param Redirect $redirect redirect service
     * @param Mailer $mailer mailer service
     * @param Random $random random generator
     * @param UrlUtils $urlUtils Url utilities
     * @param Logger $logger logger
     */
    public function __construct(
        AccountDAO $accountDAO,
        PersonDAO $personDAO,
        Redirect $redirect,
        Mailer $mailer,
        Random $random,
        UrlUtils $urlUtils,
        Logger $logger
    ) {
        $this->accountDAO = $accountDAO;
        $this->personDAO = $personDAO;
        $this->redirect = $redirect;
        $this->mailer = $mailer;
        $this->random = $random;
        $this->urlUtils = $urlUtils;
        $this->logger = $logger;
    }


    /**
     * Signs up a new user. If the data is valid, an email is sent to the user with a link to activate the account.
     * @param array $parameters the parameters of the request
     * @return string error message or empty string if no error
     */
    public function signup(array $parameters): string
    {

        $email = strtolower($parameters['email'] ?? '');
        $password = $parameters['password'] ?? '';
        $passwordConfirm = $parameters['password-confirm'] ?? '';
        $firstname = $parameters['firstname'] ?? '';
        $lastname = $parameters['lastname'] ?? '';
        $person = $this->personDAO->getPerson(new Identity($firstname, $lastname));

        $error = $this->buildError($email, $password, $passwordConfirm, $lastname, $firstname, $person);

        if (empty($error)) {
            $account = new Account($person->getId(), $email, $person, new Password($password));
            $token = $this->random->generate(10);
            $this->accountDAO->createTemporaryAccount($account, $token);
            $url = $this->urlUtils->getBaseUrl() . $this->urlUtils->buildUrl('signup_validation', ['token' => $token]);
            $this->mailer->send(
                $email,
                'Parraindex : inscription',
                "Bonjour $firstname $lastname,<br><br>Votre demande d'inscription a bien été enregistrée, merci de "
                . "cliquer sur ce lien pour la valider : <a href=\"$url\">$url</a><br><br>Cordialement"
                . "<br>Le Parrainboss"
            );
            $this->logger->info(SignupService::class, 'Signup request sent to ' . $email);
            $this->redirect->redirect('signup_confirmation');
            return '';
        }

        $this->logger->error(SignupService::class, $error . ' (' . implode(' ', $parameters) . ')');
        return $error;
    }


    /**
     * Checks if the data is valid and returns an error message if not
     * @param string $email the email given in data
     * @param string $password the password given in data
     * @param string $passwordConfirm the password confirmation given in data
     * @param string $lastname the lastname given in data
     * @param string $firstname the firstname given in data
     * @param Person|null $person the person corresponding to the given name
     * @return string error message or empty string if no error
     */
    private function buildError(
        string $email,
        string $password,
        string $passwordConfirm,
        string $lastname,
        string $firstname,
        ?Person $person
    ): string {

        $error = '';

        if (empty($error) && $this->empty($email, $password, $passwordConfirm, $lastname, $firstname)) {
            $error = 'Veuillez remplir tous les champs';
        }

        if (empty($error) && !str_ends_with($email, '@etu.univ-lyon1.fr')) {
            $error = 'L\'email doit doit être votre email universitaire';
        }

        if (empty($error) && $password !== $passwordConfirm) {
            $error = 'Les mots de passe ne correspondent pas';
        }

        if (empty($error) && $person === null) {
            $error = 'Votre nom n\'est pas enregistré, merci de faire une demande de création de personne';
        }

        $emailAccountExists = empty($error) && $this->accountDAO->existsAccount($email);
        if ($emailAccountExists) {
            $error = 'Un compte existe déjà avec cette adresse email';
        }

        $accountExistsClosure = fn() => $this->accountDAO->existsAccountByIdentity(new Identity($firstname, $lastname));
        if (empty($error) && $accountExistsClosure()) {
            $error = 'Un compte existe déjà avec ce nom';
        }

        if (!empty($error)) {
            $this->logger->error(
                SignupService::class,
                $error . ' (' . implode(' ', [$firstname, $lastname, $email, $password, $passwordConfirm]) . ')'
            );
            return $error;
        }

        $identities = $this->personDAO->getAllIdentities();
        $emailLevenshtein = preg_replace(self::NON_LETTER_REGEX, '', explode('@', $email)[0]);
        $nameLevenshtein = preg_replace(self::NON_LETTER_REGEX, '', strtolower($firstname . $lastname));
        $entryLevenshtein = levenshtein($emailLevenshtein, $nameLevenshtein);
        $minLevenshtein = $entryLevenshtein;

        if (2 < $entryLevenshtein) {
            $error = 'D\'après notre recherche, cet email n\'est pas le vôtre';
        }

        foreach ($identities as $identity) {
            $pregReplace = preg_replace(
                self::NON_LETTER_REGEX,
                '',
                strtolower($identity->getFirstname() . $identity->getLastname())
            );
            $levenshtein = levenshtein($emailLevenshtein, $pregReplace);
            if ($levenshtein < $minLevenshtein) {
                $error = 'D\'après notre recherche, cet email n\'est pas le vôtre';
            }
        }

        return $error;
    }


    /**
     * Checks if the given parameters are all not empty
     * @param string[] $parameters the parameters to check
     * @return bool true if at least one parameter is empty, false otherwise
     */
    private function empty(string ...$parameters): bool
    {
        foreach ($parameters as $parameter) {
            if (empty($parameter)) {
                return true;
            }
        }
        return false;
    }


    /**
     * Validates the account corresponding to the given token and creates the account if it is valid
     * @param string $token the token corresponding to the account to validate
     * @return string error message or empty string if no error
     */
    public function validate(string $token): string
    {
        $error = '';

        $account = $this->accountDAO->getTemporaryAccountByToken($token);

        if ($account->getId() === -1) {
            $this->logger->error(SignupService::class, 'Token invalid');
            $error = 'Ce lien n\'est pas ou plus valide.';
        }

        if (empty($error)) {
            $this->accountDAO->createAccount($account);
            $this->accountDAO->deleteTemporaryAccount($account);
            $this->mailer->send(
                $account->getLogin(),
                'Parraindex : inscription validée',
                "Bonjour,<br><br>Votre inscription a bien été validée<br><br>Cordialement<br>Le Parrainboss"
            );
            $this->logger->info(SignupService::class, 'Account created for ' . $account->getLogin());
        }

        return $error;
    }
}
