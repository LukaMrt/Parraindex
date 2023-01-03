<?php

namespace App\application\contact\executor;

use App\application\contact\ContactDAO;
use App\application\contact\field\CustomField;
use App\application\contact\field\EmailField;
use App\application\contact\field\Field;
use App\application\person\PersonDAO;
use App\application\redirect\Redirect;
use App\model\contact\ContactType;
use App\model\contact\PersonContact;

class ChockingContentContactExecutor extends ContactExecutor
{
    private PersonDAO $personDAO;


    public function __construct(ContactDAO $contactDAO, Redirect $redirect, PersonDAO $personDAO)
    {
        $personExistsClosure = fn($value) => $this->personDAO->getPersonById($value) !== null;

        parent::__construct($contactDAO, $redirect, ContactType::CHOCKING_CONTENT, [
            new Field('senderFirstName', 'Votre prénom doit contenir au moins 1 caractère'),
            new Field('senderLastName', 'Votre nom doit contenir au moins 1 caractère'),
            new EmailField('senderEmail', 'Votre email doit être valide'),
            new Field('personId', 'La personne doit être valide'),
            new CustomField('personId', 'La personne doit exister', $personExistsClosure),
            new Field('message', 'La description doit contenir au moins 1 caractère'),
        ]);
        $this->personDAO = $personDAO;
    }


    public function executeSuccess(array $data): string
    {

        $person = $this->personDAO->getPersonById($data['personId']);

        $contact = new PersonContact(
            -1,
            $data['senderFirstName'] . ' ' . $data['senderLastName'],
            $data['senderEmail'],
            ContactType::CHOCKING_CONTENT,
            $data['message'],
            $person
        );

        $this->contactDAO->saveChockingContentContact($contact);
        return '';
    }
}
