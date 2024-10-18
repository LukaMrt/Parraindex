<?php

declare(strict_types=1);

namespace App\Application\contact\executor;

use App\Application\contact\ContactDAO;
use App\Application\contact\field\EmailField;
use App\Application\contact\field\Field;
use App\Application\contact\field\YearField;
use App\Application\person\PersonDAO;
use App\Application\redirect\Redirect;
use App\Entity\Contact\Type;
use App\Entity\old\contact\PersonContact;
use App\Entity\old\person\Identity;
use App\Entity\old\person\PersonBuilder;

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

        parent::__construct($contactDAO, $redirect, Type::ADD_PERSON, [
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
    #[\Override]
    public function executeSuccess(array $data): string
    {

        if ($this->personDAO->getPerson(new Identity($data['creationFirstName'], $data['creationLastName'])) instanceof \App\Entity\old\person\Person) {
            return 'La personne ne doit pas exister';
        }

        $person = PersonBuilder::aPerson()
            ->withId(-1)
            ->withIdentity(new Identity($data['creationFirstName'], $data['creationLastName']))
            ->withStartYear($data['entryYear'])
            ->build();

        $personContact = new PersonContact(
            -1,
            date('Y-m-d'),
            null,
            $data['senderFirstName'] . ' ' . $data['senderLastName'],
            $data['senderEmail'],
            Type::ADD_PERSON,
            $data['bonusInformation'] ?? '',
            $person
        );

        $this->contactDAO->savePersonAddContact($personContact);
        return '';
    }
}
