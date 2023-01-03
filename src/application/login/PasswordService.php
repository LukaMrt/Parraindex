<?php

namespace App\application\login;

use App\application\mail\Mailer;
use App\application\person\PersonDAO;
use App\application\random\Random;
use App\application\redirect\Redirect;
use App\model\account\Account;
use App\model\account\Password;

class PasswordService
{

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


    public function resetPassword(array $parameters): string
    {

        if (!$this->accountDAO->existsAccount($parameters['email'])) {
            return 'Email inconnu.';
        }

        $account = $this->accountDAO->getAccountByLogin($parameters['email']);
        $person = $this->personDAO->getPersonById($account->getPersonId());
        $firstname = $person->getFirstname();
        $lastname = $person->getLastname();
        $account = new Account($account->getId(), $account->getLogin(), $person, new Password($parameters['password']));


        $token = $this->random->generate(10);
        $url = $this->urlUtils->getBaseUrl();
        $url .= $this->urlUtils->buildUrl('resetpassword_validation', ['token' => $token]);
        $this->mailer->send(
            $parameters['email'],
            'Parraindex : réinitialisation de mot de passe',
            "Bonjour $firstname $lastname,<br><br>Votre demande de réinitialisation de mot de passe a bien été "
            . "enregistrée, merci de cliquer sur ce lien pour la valider : <a href=\"$url\">$url</a><br><br>"
            . "Cordialement<br>Le Parrainboss"
        );
        $this->accountDAO->createResetpassword($account, $token);
        $this->redirect->redirect('resetpassword_confirmation');
        return '';
    }


    public function validateResetPassword(string $token): string
    {
        $error = '';

        $account = $this->accountDAO->getAccountResetPasswordByToken($token);

        if ($account->getId() === -1) {
            $error = 'Ce lien n\'est pas ou plus valide.';
        }

        if (empty($error)) {
            $this->accountDAO->editAccountPassword($account);
            $this->accountDAO->deleteResetPassword($account);
        }

        return $error;
    }

}
