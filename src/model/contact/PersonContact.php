<?php

namespace App\model\contact;

use App\model\person\Person;

class PersonContact extends Contact
{

    private Person $person;

    public function __construct(
        int         $id,
        string      $contacterName,
        string      $contacterEmail,
        ContactType $type,
        string      $description,
        Person      $person
    )
    {
        parent::__construct($id, $contacterName, $contacterEmail, $type, $description);
        $this->person = $person;
    }

    public function getDescription(): array
    {

        return [
            ['Personne concernée', $this->person->getFirstName() . ' ' . $this->person->getLastName()],
            ['Année d\'entrée à l\'IUT', $this->person->getStartYear()]
        ];
    }

    public function getPerson(): Person
    {
        return $this->person;
    }
}
