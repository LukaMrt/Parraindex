<?php

namespace App\application\login;

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
    const NON_LETTER_REGEX = "/[^a-z]/";
    private AccountDAO $accountDAO;
    private PersonDAO $personDAO;
    private Redirect $redirect;
    private Mailer $mailer;
    private Random $random;
    private UrlUtils $urlUtils;

    public function __construct(
        AccountDAO $accountDAO,
        PersonDAO  $personDAO,
        Redirect   $redirect,
        Mailer     $mailer,
        Random     $random,
        UrlUtils   $urlUtils
    )
    {
        $this->accountDAO = $accountDAO;
        $this->personDAO = $personDAO;
        $this->redirect = $redirect;
        $this->mailer = $mailer;
        $this->random = $random;
        $this->urlUtils = $urlUtils;
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
            $this->redirect->redirect('signup_confirmation');
        }

        return $error;
    }

    private function buildError(
        string  $email,
        string  $password,
        string  $passwordConfirm,
        string  $lastname,
        string  $firstname,
        ?Person $person
    ): string
    {

        if ($this->empty($email, $password, $passwordConfirm, $lastname, $firstname)) {
            return 'Veuillez remplir tous les champs';
        }

        if (!str_ends_with($email, '@etu.univ-lyon1.fr')) {
            return 'L\'email doit doit être votre email universitaire';
        }

        if ($password !== $passwordConfirm) {
            return 'Les mots de passe ne correspondent pas';
        }

        if ($person === null) {
            return 'Votre nom n\'est pas enregistré, merci de contacter un administrateur';
        }

        $emailAccountExists = $this->accountDAO->existsAccount($email);
        if ($emailAccountExists) {
            return 'Un compte existe déjà avec cette adresse email';
        }

        $nameAccountExists = $this->accountDAO->existsAccountByIdentity(new Identity($firstname, $lastname));
        if ($nameAccountExists) {
            return 'Un compte existe déjà avec ce nom';
        }

        $identities = $this->personDAO->getAllIdentities();
        $emailLevenshtein = preg_replace(self::NON_LETTER_REGEX, '', explode('@', $email)[0]);
        $nameLevenshtein = preg_replace(self::NON_LETTER_REGEX, '', strtolower($firstname . $lastname));
        $entryLevenshtein = levenshtein($emailLevenshtein, $nameLevenshtein);
        $minLevenshtein = $entryLevenshtein;

        if (2 < $entryLevenshtein) {
            return 'D\'après notre recherche, cet email n\'est pas le vôtre';
        }

        foreach ($identities as $identity) {
            $pregReplace = preg_replace(
                self::NON_LETTER_REGEX,
                '',
                strtolower($identity->getFirstname() . $identity->getLastname())
            );
            $levenshtein = levenshtein($emailLevenshtein, $pregReplace);
            if ($levenshtein < $minLevenshtein) {
                return 'D\'après notre recherche, cet email n\'est pas le vôtre';
            }
        }

        return '';
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
            $error = 'Ce lien n\'est pas ou plus valide.';
        }

        if (empty($error)) {
            $this->accountDAO->createAccount($account);
            $this->accountDAO->deleteTemporaryAccount($account);
        }

        return $error;
    }
}
