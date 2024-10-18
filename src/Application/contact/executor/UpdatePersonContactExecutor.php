<?php

declare(strict_types=1);

namespace App\Application\contact\executor;

use App\Application\contact\ContactDAO;
use App\Application\contact\field\CustomField;
use App\Application\contact\field\EmailField;
use App\Application\contact\field\Field;
use App\Application\person\PersonDAO;
use App\Application\redirect\Redirect;
use App\Entity\Contact\Type;
use App\Entity\old\contact\PersonContact;

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
        $personExistsClosure = fn($value): bool => $this->personDAO->getPersonById($value) instanceof \App\Entity\old\person\Person;

        parent::__construct($contactDAO, $redirect, Type::UPDATE_PERSON, [
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
    #[\Override]
    public function executeSuccess(array $data): string
    {

        $person = $this->personDAO->getPersonById($data['personId']);

        $personContact = new PersonContact(
            -1,
            date('Y-m-d'),
            null,
            $data['senderFirstName'] . ' ' . $data['senderLastName'],
            $data['senderEmail'],
            Type::UPDATE_PERSON,
            $data['message'] ?? '',
            $person
        );

        $this->contactDAO->savePersonUpdateContact($personContact);
        return '';
    }
}
