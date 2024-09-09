<?php

namespace App\Application\contact\executor;

use App\Application\contact\ContactDAO;
use App\Application\contact\field\EmailField;
use App\Application\contact\field\Field;
use App\Application\login\AccountDAO;
use App\Application\login\UrlUtils;
use App\Application\person\PersonDAO;
use App\Application\random\Random;
use App\Application\redirect\Redirect;
use App\Entity\ContactType;
use App\Entity\old\account\Account;
use App\Entity\old\account\Password;
use App\Entity\old\contact\PersonContact;
use App\Entity\old\person\Identity;

class PasswordContactExecutor extends ContactExecutor
{
    /**
     * @var PersonDAO DAO for person
     */
    private PersonDAO $personDAO;
    /**
     * @var AccountDAO DAO for account
     */
    private AccountDAO $accountDAO;
    /**
     * @var Random Random generator service
     */
    private Random $random;
    /**
     * @var UrlUtils Url utilities
     */
    private UrlUtils $urlUtils;


    /**
     * @param ContactDAO $contactDAO DAO for contacts
     * @param Redirect $redirect Redirect service
     * @param PersonDAO $personDAO DAO for persons
     * @param AccountDAO $accountDAO DAO for accounts
     * @param Random $random Random generator service
     * @param UrlUtils $urlUtils Url utilities
     */
    public function __construct(
        ContactDAO $contactDAO,
        Redirect $redirect,
        PersonDAO $personDAO,
        AccountDAO $accountDAO,
        Random $random,
        UrlUtils $urlUtils
    ) {
        parent::__construct($contactDAO, $redirect, ContactType::PASSWORD, [
            new Field('senderFirstName', 'Votre prénom doit contenir au moins 1 caractère'),
            new Field('senderLastName', 'Votre nom doit contenir au moins 1 caractère'),
            new EmailField('senderEmail', 'Votre email doit être valide'),
            new Field('password', 'Le mot de passe doit contenir au moins 1 caractère'),
        ]);
        $this->personDAO = $personDAO;
        $this->accountDAO = $accountDAO;
        $this->random = $random;
        $this->urlUtils = $urlUtils;
    }


    public function executeSuccess(array $data): string
    {

        if ($data['password'] !== $data['passwordConfirm']) {
            return 'Les mots de passe doivent être identiques';
        }

        $sender = new Identity($data['senderFirstName'], $data['senderLastName']);

        $error = "";
        $person = $this->personDAO->getPerson($sender);

        if ($person === null) {
            $error = 'Cette carte n\'est pas enregistrée, veuillez faire une demande de création de personne avant';
        } elseif ($this->accountDAO->existsAccount($data['senderEmail'])) {
            $error = 'Cet email est déjà associée à un compte';
        } elseif ($this->accountDAO->existsAccountByIdentity($sender)) {
            $error = 'Cette carte est déjà associée à un compte';
        }

        if ($error !== "") {
            return $error;
        }

        $account = new Account($person->getId(), $data['senderEmail'], $person, new Password($data['password']));
        $token = $this->random->generate(10);
        $this->accountDAO->createTemporaryAccount($account, $token);

        $contact = new PersonContact(
            -1,
            date('Y-m-d'),
            null,
            $data['senderFirstName'] . ' ' . $data['senderLastName'],
            $data['senderEmail'],
            ContactType::PASSWORD,
            $this->urlUtils->getBaseUrl() . $this->urlUtils->buildUrl('signup_validation', ['token' => $token]),
            $person
        );

        $this->contactDAO->savePersonUpdateContact($contact);
        return '';
    }
}
