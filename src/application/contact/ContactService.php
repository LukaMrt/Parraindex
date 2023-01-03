<?php

namespace App\application\contact;

use App\application\contact\executor\ContactExecutors;
use App\model\contact\Contact;

class ContactService
{
    private ContactExecutors $contactExecutors;
    private ContactDAO $contactDAO;


    public function __construct(ContactExecutors $contactExecutors, ContactDAO $contactDAO)
    {
        $this->contactExecutors = $contactExecutors;
        $this->contactDAO = $contactDAO;
    }


    public function registerContact(array $parameters): string
    {

        $id = -1;

        if (isset($parameters['type']) && is_numeric($parameters['type'])) {
            $id = $parameters['type'];
        }

        $executors = array_values($this->contactExecutors->getExecutorsById($id));

        if (empty($executors)) {
            return 'Le type de contact n\'est pas valide.';
        }

        return $executors[0]->execute($parameters);
    }


    public function closeContact(int $contactId, int $resolverId): void
    {
        $this->contactDAO->closeContact($contactId, $resolverId);
    }


    public function getContact(int $id): Contact
    {
        return array_values(array_filter($this->getContactList(), fn($contact) => $contact->getId() === $id))[0];
    }


    public function getContactList(): array
    {
        return $this->contactDAO->getContactList();
    }
}
