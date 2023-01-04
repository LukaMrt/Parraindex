<?php

namespace App\application\login;

use App\application\logging\Logger;
use App\application\mail\Mailer;
use App\application\person\PersonDAO;
use App\application\random\Random;
use App\application\redirect\Redirect;
use App\model\account\Account;
use App\model\account\Password;
use App\model\person\Identity;
use App\model\person\Person;

class SignupService
{
    private const NON_LETTER_REGEX = "/[^a-z]/";
    private AccountDAO $accountDAO;
    private PersonDAO $personDAO;
    private Redirect $redirect;
    private Mailer $mailer;
    private Random $random;
    private UrlUtils $urlUtils;
    private Logger $logger;


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
        }

        $this->logger->error(SignupService::class, $error . ' (' . implode(' ', $parameters) . ')');
        return $error;
    }


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
            $error = 'Votre nom n\'est pas enregistré, merci de contacter un administrateur';
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
                $error . ' (' . implode(' ', [$email, $password, $passwordConfirm, $lastname, $firstname]) . ')'
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


    private function empty(string ...$parameters): bool
    {
        foreach ($parameters as $parameter) {
            if (empty($parameter)) {
                return true;
            }
        }
        return false;
    }


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
            $this->logger->info(SignupService::class, 'Account created for ' . $account->getLogin());
        }

        return $error;
    }
}
