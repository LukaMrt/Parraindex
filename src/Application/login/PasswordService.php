<?php

declare(strict_types=1);

namespace App\Application\login;

use App\Application\logging\Logger;
use App\Application\mail\Mailer;
use App\Application\person\PersonDAO;
use App\Application\random\Random;
use App\Application\redirect\Redirect;
use App\Entity\old\account\Account;
use App\Entity\old\account\Password;

/**
 * Service for managing passwords (resetting, changing, etc.)
 */
class PasswordService
{
    /**
     * @var AccountDAO DAO for accounts
     */
    private AccountDAO $accountDAO;

    /**
     * @var PersonDAO DAO for persons
     */
    private PersonDAO $personDAO;

    /**
     * @var Redirect Redirect service
     */
    private Redirect $redirect;

    /**
     * @var Mailer Mailer service
     */
    private Mailer $mailer;

    /**
     * @var Random Random generator
     */
    private Random $random;

    /**
     * @var UrlUtils URL utilities
     */
    private UrlUtils $urlUtils;

    /**
     * @var Logger Logger
     */
    private Logger $logger;


    /**
     * @param AccountDAO $accountDAO DAO for accounts
     * @param PersonDAO $personDAO DAO for persons
     * @param Redirect $redirect Redirect service
     * @param Mailer $mailer Mailer service
     * @param Random $random Random generator
     * @param UrlUtils $urlUtils URL utilities
     * @param Logger $logger Logger
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
        $this->personDAO  = $personDAO;
        $this->redirect   = $redirect;
        $this->mailer     = $mailer;
        $this->random     = $random;
        $this->urlUtils   = $urlUtils;
        $this->logger     = $logger;
    }


    /**
     * Start the password reset process. It checks if the information provided is valid and sends an email to the user
     * with a link to reset the password.
     * @param array $parameters Parameters from the request
     * @return string Error message if any, empty string otherwise
     */
    public function resetPassword(array $parameters): string
    {

        if (!$this->accountDAO->existsAccount($parameters['email'])) {
            $this->logger->error(PasswordService::class, 'Email not found');
            return 'Email inconnu.';
        }

        $account   = $this->accountDAO->getAccountByLogin($parameters['email']);
        $person    = $this->personDAO->getPersonById($account->getPersonId());
        $firstname = $person->getFirstname();
        $lastname  = $person->getLastname();
        $account   = new Account(
            $account->getId(),
            $account->getLogin(),
            $person,
            new Password($parameters['password']),
            $account->getRole(),
        );

        $token = $this->random->generate(10);
        $url   = $this->urlUtils->getBaseUrl();
        $url  .= $this->urlUtils->buildUrl('resetpassword_validation', ['token' => $token]);
        $this->mailer->send(
            $parameters['email'],
            'Parraindex : réinitialisation de mot de passe',
            sprintf('Bonjour %s %s,<br><br>Votre demande de réinitialisation de mot de passe a bien été ', $firstname, $lastname)
            . sprintf('enregistrée, merci de cliquer sur ce lien pour la valider : <a href="%s">%s</a><br><br>', $url, $url)
            . "Cordialement<br>Le Parrainboss"
        );
        $this->accountDAO->createResetpassword($account, $token);
        $this->logger->info(PasswordService::class, 'Reset password email sent to ' . $parameters['email']);
        $this->redirect->redirect('resetpassword_confirmation');
        return '';
    }


    /**
     * Validate the password reset process. It checks if the token is valid and if the password is valid. If so, it
     * updates the password.
     * @param string $token Token from the request
     * @return string Error message if any, empty string otherwise
     */
    public function validateResetPassword(string $token): string
    {
        $error = '';

        $account = $this->accountDAO->getAccountResetPasswordByToken($token);

        if ($account->getId() === -1) {
            $this->logger->error(PasswordService::class, 'Token invalid');
            $error = "Ce lien n'est pas ou plus valide.";
        }

        if ($error === '' || $error === '0') {
            $this->accountDAO->editAccountPassword($account);
            $this->accountDAO->deleteResetPassword($account);
            $this->logger->info(PasswordService::class, 'Password reset for ' . $account->getLogin());
        }

        return $error;
    }
}
