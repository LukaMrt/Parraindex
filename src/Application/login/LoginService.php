<?php

namespace App\Application\login;

use App\Application\logging\Logger;
use App\Application\person\PersonDAO;
use App\Application\redirect\Redirect;
use App\Entity\old\account\Password;
use JetBrains\PhpStorm\NoReturn;

/**
 * Service to manage login and logout actions.
 */
class LoginService
{
    /**
     * @var AccountDAO DAO for account
     */
    private AccountDAO $accountDAO;
    /**
     * @var Redirect Redirect service
     */
    private Redirect $redirect;
    /**
     * @var SessionManager Session manager
     */
    private SessionManager $sessionManager;
    /**
     * @var PersonDAO DAO for person
     */
    private PersonDAO $personDAO;
    /**
     * @var Logger Logger
     */
    private Logger $logger;


    /**
     * @param AccountDAO $accountDAO DAO for account
     * @param PersonDAO $personDAO DAO for person
     * @param Redirect $redirect Redirect service
     * @param SessionManager $sessionManager Session manager
     * @param Logger $logger Logger
     */
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


    /**
     * Login a person in if the email and password are correct
     * @param array $parameters form parameters
     * @return string error message or empty string if no error
     */
    public function login(array $parameters): string
    {
        $action = $parameters['action'] ?? 'login';
        if ($action === 'register') {
            $this->redirect->redirect('signup_get');
            return '';
        }

        $error = $this->checkLogin($parameters);
        if (empty($error)) {
            $account = $this->accountDAO->getAccountByLogin($parameters['login']);

            $this->sessionManager->set('login', $account->getLogin());
            $this->sessionManager->set('privilege', $account->getRole()->toString());
            $this->sessionManager->set('user', $this->personDAO->getPersonByLogin($account->getLogin()));

            $this->logger->info(LoginService::class, 'User ' . $account->getLogin() . ' logged in');
            $this->redirect->redirect('home');
            return '';
        }

        $this->logger->error(LoginService::class, $error . ' (' . implode(' ', $parameters) . ')');
        return $error;
    }


    /**
     * Check if the given parameters are correct to log the person in
     * @param array $parameters form parameters
     * @return string error message or empty string if no error
     */
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


    /**
     * Logout the current user by destroying the session
     * @return void
     */
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
