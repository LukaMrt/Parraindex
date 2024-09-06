<?php

namespace App\Application\contact\executor;

use App\Application\contact\ContactDAO;
use App\Application\contact\field\CustomField;
use App\Application\contact\field\EmailField;
use App\Application\contact\field\Field;
use App\Application\person\PersonDAO;
use App\Application\redirect\Redirect;
use App\Entity\contact\ContactType;
use App\Entity\contact\PersonContact;

/**
 * Contact executor for the reporting of a chocking content
 */
class ChockingContentContactExecutor extends ContactExecutor
{
    /**
     * @var PersonDAO $personDAO DAO for person
     */
    private PersonDAO $personDAO;


    /**
     * @param ContactDAO $contactDAO DAO for contact
     * @param Redirect $redirect Redirect service
     * @param PersonDAO $personDAO DAO for person
     */
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


    /**
     * Performs the actions when the form is valid
     * @param array $data
     * @return string
     */
    public function executeSuccess(array $data): string
    {

        $person = $this->personDAO->getPersonById($data['personId']);

        $contact = new PersonContact(
            -1,
            date('Y-m-d'),
            null,
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
