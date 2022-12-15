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

class SignupService {

	private AccountDAO $accountDAO;
	private PersonDAO $personDAO;
	private Redirect $redirect;
	private Mailer $mailer;
	private Random $random;
	private UrlUtils $urlUtils;

	public function __construct(AccountDAO $accountDAO, PersonDAO $personDAO, Redirect $redirect, Mailer $mailer, Random $random, UrlUtils $urlUtils) {
		$this->accountDAO = $accountDAO;
		$this->personDAO = $personDAO;
		$this->redirect = $redirect;
		$this->mailer = $mailer;
		$this->random = $random;
		$this->urlUtils = $urlUtils;
	}

	public function signup(array $parameters): string {

		$email = $parameters['email'] ?? '';
		$password = $parameters['password'] ?? '';
		$passwordConfirm = $parameters['password-confirm'] ?? '';
		$lastname = $parameters['lastname'] ?? '';
		$firstname = $parameters['firstname'] ?? '';
		$person = $this->personDAO->getPerson(new Identity($firstname, $lastname));

		$error = $this->buildError($email, $password, $passwordConfirm, $lastname, $firstname, $person);

		if (empty($error)) {
			$account = new Account($person->getId(), $email, $person, new Password($password));
			$token = $this->random->generate(10);
			$this->accountDAO->createTemporaryAccount($account, $token);
			$url = $this->urlUtils->getBaseUrl();
			$this->mailer->send($email, 'Parraindex : inscription', "Bonjour $firstname $lastname,<br><br>Votre demande d'inscription a bien été enregistrée, merci de cliquer sur ce lien pour valider votre inscription : <a href=\"$url/signupConfirmation/$token\">$url/signupConfirmation/$token</a><br><br>Cordialement<br>Le Parrainboss");
			$this->redirect->redirect('signup_confirmation');
		}

		return $error;
	}

	private function buildError(mixed $email, mixed $password, mixed $passwordConfirm, mixed $lastname, mixed $firstname, ?Person $person): string {

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
		$emailLevenshtein = preg_replace("/[^a-z]/", '', explode('@', $email)[0]);
		$nameLevenshtein = preg_replace("/[^a-z]/", '', $firstname . $lastname);
		$entryLevenshtein = levenshtein($emailLevenshtein, $nameLevenshtein);
		$minLevenshtein = $entryLevenshtein;

		foreach ($identities as $identity) {
			$levenshtein = levenshtein($emailLevenshtein, preg_replace("/[^a-z]/", '', $identity->getFirstname() . $identity->getLastname()));
			if ($levenshtein < $minLevenshtein) {
				$minLevenshtein = $levenshtein;
			}
		}

		if ($minLevenshtein != $entryLevenshtein || 2 < $entryLevenshtein) {
			return 'D\'après notre recherche, cet email n\'est pas le vôtre';
		}

		return '';
	}

	private function empty(string...$parameters): bool {
		foreach ($parameters as $parameter) {
			if (empty($parameter)) {
				return true;
			}
		}
		return false;
	}

	public function validate(string $token): string {
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
