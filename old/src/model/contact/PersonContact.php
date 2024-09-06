<?php

namespace App\model\contact;

use App\model\person\Person;

/**
 * Contact requests related to a person
 */
class PersonContact extends Contact
{
    /**
     * @var Person The person related to the contact request
     */
    private Person $person;


    /**
     * @param int $id The id of the contact request
     * @param string $contactDate The date where the contact message was sent
     * @param ?string $contactResolution The date where the contact message was resolved
     * @param string $contacterName The name of the person who sent the contact request
     * @param string $contacterEmail The email of the person who sent the contact request
     * @param ContactType $type The type of the contact request
     * @param string $description The description of the contact request
     * @param Person $person The person related to the contact request
     */
    public function __construct(
        int $id,
        string $contactDate,
        ?string $contactResolution,
        string $contacterName,
        string $contacterEmail,
        ContactType $type,
        string $description,
        Person $person
    ) {
        parent::__construct(
            $id,
            $contactDate,
            $contactResolution,
            $contacterName,
            $contacterEmail,
            $type,
            $description
        );
        $this->person = $person;
    }


    /**
     * @return array[] The description of the contact request
     */
    public function getDescription(): array
    {

        return [
            ['Personne concernée', $this->person->getFirstName() . ' PersonContact.php' . $this->person->getLastName()],
            ['Année d\'entrée à l\'IUT', $this->person->getStartYear()]
        ];
    }


    /**
     * @return Person The person related to the contact request
     */
    public function getPerson(): Person
    {
        return $this->person;
    }
}
