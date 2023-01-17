<?php

namespace App\application\contact\executor;

use App\application\contact\ContactDAO;
use App\application\contact\field\EmailField;
use App\application\contact\field\Field;
use App\application\contact\field\YearField;
use App\application\person\PersonDAO;
use App\application\redirect\Redirect;
use App\model\contact\ContactType;
use App\model\contact\PersonContact;
use App\model\person\Identity;
use App\model\person\PersonBuilder;

/**
 * Contact executor for the adding of a person
 */
class AddPersonContactExecutor extends ContactExecutor
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

        parent::__construct($contactDAO, $redirect, ContactType::ADD_PERSON, [
            new Field('senderFirstName', 'Votre prénom doit contenir au moins 1 caractère'),
            new Field('senderLastName', 'Votre nom doit contenir au moins 1 caractère'),
            new EmailField('senderEmail', 'Votre email doit être valide'),
            new Field('creationFirstName', 'Le prénom doit contenir au moins 1 caractère'),
            new Field('creationLastName', 'Le nom doit contenir au moins 1 caractère'),
            new YearField('entryYear', 'L\'année doit être valide'),
        ]);
        $this->personDAO = $personDAO;
    }


    /**
     * Executes actions for adding a sponsor. I basically store the request through the DAO
     * @param array $data Form data
     * @return string Error message or empty string if no error
     */
    public function executeSuccess(array $data): string
    {

        if ($this->personDAO->getPerson(new Identity($data['creationFirstName'], $data['creationLastName'])) !== null) {
            return 'La personne ne doit pas exister';
        }

        $person = PersonBuilder::aPerson()
            ->withId(-1)
            ->withIdentity(new Identity($data['creationFirstName'], $data['creationLastName']))
            ->withStartYear($data['entryYear'])
            ->build();

        $contact = new PersonContact(
            -1,
            date('Y-m-d'),
            null,
            $data['senderFirstName'] . ' ' . $data['senderLastName'],
            $data['senderEmail'],
            ContactType::ADD_PERSON,
            $data['bonusInformation'] ?? '',
            $person
        );

        $this->contactDAO->savePersonAddContact($contact);
        return '';
    }
}
