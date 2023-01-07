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

/**
 * Contact executor for the updating of a person
 */
class UpdatePersonContactExecutor extends ContactExecutor
{
    /**
     * @var PersonDAO DAO for persons
     */
    private PersonDAO $personDAO;


    /**
     * @param PersonDAO $personDAO DAO for persons
     * @param ContactDAO $contactDAO DAO for contacts
     * @param Redirect $redirect Redirect service
     */
    public function __construct(PersonDAO $personDAO, ContactDAO $contactDAO, Redirect $redirect)
    {
        $personExistsClosure = fn($value) => $this->personDAO->getPersonById($value) !== null;

        parent::__construct($contactDAO, $redirect, ContactType::UPDATE_PERSON, [
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
     * @param array $data Form data
     * @return string Error message or empty string if no error
     */
    public function executeSuccess(array $data): string
    {

        $person = $this->personDAO->getPersonById($data['personId']);

        $contact = new PersonContact(
            -1,
            $data['senderFirstName'] . ' ' . $data['senderLastName'],
            $data['senderEmail'],
            ContactType::UPDATE_PERSON,
            $data['message'] ?? '',
            $person
        );

        $this->contactDAO->savePersonUpdateContact($contact);
        return '';
    }
}
