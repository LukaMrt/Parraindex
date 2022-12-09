<?php

namespace App\application\login;

use App\application\person\PersonDAO;
use App\application\redirect\Redirect;
use App\model\account\Account;
use App\model\account\Password;
use App\model\person\Identity;

class LoginService {

    private AccountDAO $accountDAO;
    private PersonDAO $personDAO;
    private Redirect $redirect;
    private SessionManager $sessionManager;

    public function __construct(AccountDAO $accountDAO, PersonDAO $personDAO, Redirect $redirect, SessionManager $sessionManager) {
        $this->accountDAO = $accountDAO;
        $this->personDAO = $personDAO;
        $this->redirect = $redirect;
        $this->sessionManager = $sessionManager;
    }

    public function login(array $parameters): string {
        $action = $parameters['action'] ?? 'login';
        if ($action === 'register') {
            $this->redirect->redirect('signup_get');
            return '';
        }

        $error = $this->checkLogin($parameters);
        if (empty($error)) {
            $account = $this->accountDAO->getSimpleAccount($parameters['login']);
            $this->sessionManager->set('login', $account->getLogin());
            $this->sessionManager->set('privilege', $account->getHighestPrivilege()->toString());
            $this->redirect->redirect('home');
        }

        return $error;
    }

    private function checkLogin(array $parameters): string {

        $login = $parameters['login'] ?? '';
        $password = new Password($parameters['password'] ?? '');
        $realPassword = $this->accountDAO->getAccountPassword($login);

        $errors = [
            [
                'condition' => empty($login) || $password->isEmpty(),
                'message' => 'Veuillez remplir tous les champs'
            ],
            [
                'condition' => $realPassword->isEmpty(),
                'message' => 'Identifiant incorrect'
            ],
            [
                'condition' => !$password->check($realPassword->getPassword()),
                'message' => 'Mot de passe incorrect'
            ]
        ];

        $errors = array_filter($errors, fn($error) => $error['condition']);
        $errors = array_map(fn($error) => $error['message'], $errors);

        return array_shift($errors) ?? '';
    }

    public function signup(array $parameters): string {

        $error = '';
        $email = $parameters['email'] ?? '';
        $password = $parameters['password'] ?? '';
        $passwordConfirm = $parameters['password-confirm'] ?? '';
        $lastname = $parameters['lastname'] ?? '';
        $firstname = $parameters['firstname'] ?? '';
        $person = $this->personDAO->getPerson(new Identity($firstname, $lastname));
        $emailAccountExists = $this->accountDAO->existsAccount($email);
        $nameAccountExists = $this->accountDAO->existsAccountByIdentity(new Identity($firstname, $lastname));

        if ($this->empty($email, $password, $passwordConfirm, $lastname, $firstname)) {
            $error = 'Veuillez remplir tous les champs';
        }

        if (empty($error) && $password !== $passwordConfirm) {
            $error = 'Les mots de passe ne correspondent pas';
        }

        if (empty($error) && $person === null) {
            $error = 'Votre nom n\'est pas enregistré, merci de contacter un administrateur';
        }

        if (empty($error) && $emailAccountExists) {
            $error = 'Un compte existe déjà avec cette adresse email';
        }

        if (empty($error) && $nameAccountExists) {
            $error = 'Un compte existe déjà avec ce nom';
        }

        if (empty($error)) {
            $account = new Account(-1, $email, $person, new Password($password));
            $this->redirect->redirect('home');
            $this->accountDAO->createAccount($account);
        }

        return $error;
    }

    private function empty(string...$parameters): bool {
        foreach ($parameters as $parameter) {
            if (empty($parameter)) {
                return true;
            }
        }
        return false;
    }

}