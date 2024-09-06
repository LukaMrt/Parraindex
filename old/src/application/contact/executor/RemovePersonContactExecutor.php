<?php

namespace App\application\contact\executor;

use App\application\contact\ContactDAO;
use App\application\contact\field\CustomField;
use App\application\contact\field\EmailField;
use App\application\contact\field\Field;
use App\application\contact\field\NumberField;
use App\application\person\PersonDAO;
use App\application\redirect\Redirect;
use App\model\contact\ContactType;
use App\model\contact\PersonContact;

/**
 * Contact executor for the removing of a person
 */
class RemovePersonContactExecutor extends ContactExecutor
{
    /**
     * @var PersonDAO $personDAO DAO for person
     */
    private PersonDAO $personDAO;


    /**
     * @param PersonDAO $personDAO DAO for person
     * @param ContactDAO $contactDAO DAO for contact
     * @param Redirect $redirect Redirect service
     */
    public function __construct(PersonDAO $personDAO, ContactDAO $contactDAO, Redirect $redirect)
    {
        $personExistsClosure = fn($value) => $this->personDAO->getPersonById($value) !== null;

        parent::__construct($contactDAO, $redirect, ContactType::REMOVE_PERSON, [
            new Field('senderFirstName', 'Votre prénom doit contenir au moins 1 caractère'),
            new Field('senderLastName', 'Votre nom doit contenir au moins 1 caractère'),
            new EmailField('senderEmail', 'Votre email doit être valide'),
            new NumberField('personId', 'La personne doit être valide'),
            new CustomField('personId', 'La personne doit exister', $personExistsClosure),
            new Field('message', 'La description doit contenir au moins 1 caractère'),
        ]);
        $this->personDAO = $personDAO;
    }


    /**
     * Execute the actions when the form is valid
     * @param array $data form data
     * @return string error message or empty string if no error
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
            ContactType::REMOVE_PERSON,
            $data['message'],
            $person
        );

        $this->contactDAO->savePersonRemoveContact($contact);
        return '';
    }
}
