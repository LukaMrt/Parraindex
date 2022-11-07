<?php

namespace App\application\login;

use App\application\redirect\Redirect;
use App\model\account\Password;

class LoginService {

    private AccountDAO $accountDAO;
    private Redirect $redirect;
    private SessionManager $sessionManager;

    public function __construct(AccountDAO $accountDAO, Redirect $redirect, SessionManager $sessionManager) {
        $this->accountDAO = $accountDAO;
        $this->redirect = $redirect;
        $this->sessionManager = $sessionManager;
    }

    public function login(array $parameters): string {

        $error = $this->checkLogin($parameters);

        if (empty($error)) {
            $this->sessionManager->set('login', $parameters['login']);
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

}