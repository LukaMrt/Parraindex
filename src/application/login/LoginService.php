<?php

namespace App\application\login;

use App\application\logging\Logger;
use App\application\person\PersonDAO;
use App\application\redirect\Redirect;
use App\model\account\Password;
use JetBrains\PhpStorm\NoReturn;

class LoginService
{
    private AccountDAO $accountDAO;
    private Redirect $redirect;
    private SessionManager $sessionManager;
    private PersonDAO $personDAO;
    private Logger $logger;


    public function __construct(
        AccountDAO $accountDAO,
        PersonDAO $personDAO,
        Redirect $redirect,
        SessionManager $sessionManager,
        Logger $logger
    ) {
        $this->accountDAO = $accountDAO;
        $this->personDAO = $personDAO;
        $this->redirect = $redirect;
        $this->sessionManager = $sessionManager;
        $this->logger = $logger;
    }


    public function login(array $parameters): string
    {
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
            $this->sessionManager->set('user', $this->personDAO->getPersonByLogin($account->getLogin()));

            $this->logger->info(LoginService::class, 'User ' . $account->getLogin() . ' logged in');
            $this->redirect->redirect('home');
        }

        $this->logger->error(LoginService::class, $error . ' (' . implode(' ', $parameters) . ')');
        return $error;
    }


    private function checkLogin(array $parameters): string
    {

        $login = $parameters['login'] ?? '';
        $password = new Password($parameters['password'] ?? '');
        $realPassword = $this->accountDAO->getAccountPassword($login);

        $errors = [
            [
                'condition' => $this->sessionManager->exists('login'),
                'message' => 'Vous êtes déjà connecté'
            ],
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


    #[NoReturn] public function logout(): void
    {

        $destination = 'home';

        if ($this->sessionManager->exists('login')) {
            $destination = 'logout_confirmation';
            $this->sessionManager->destroySession();
        }

        $this->redirect->redirect($destination);
    }
}
