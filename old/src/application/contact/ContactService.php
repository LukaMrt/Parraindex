<?php

namespace App\application\contact;

use App\application\contact\executor\ContactExecutors;
use App\model\contact\Contact;

/**
 * Service to manage the contacts
 */
class ContactService
{
    /**
     * @var ContactExecutors executors
     */
    private ContactExecutors $contactExecutors;

    /**
     * @var ContactDAO DAO for the contacts
     */
    private ContactDAO $contactDAO;


    /**
     * @param ContactExecutors $contactExecutors executors
     * @param ContactDAO $contactDAO DAO for the contacts
     */
    public function __construct(ContactExecutors $contactExecutors, ContactDAO $contactDAO)
    {
        $this->contactExecutors = $contactExecutors;
        $this->contactDAO = $contactDAO;
    }


    /**
     * Creates a new contact
     *
     * @param string[] $parameters parameters to create the contact
     * @return string error message or empty string if no error
     */
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


    /**
     * Close a contact
     *
     * @param int $contactId id of the contact
     * @param int $resolverId id of the person who closed the contact
     * @return void
     */
    public function closeContact(int $contactId, int $resolverId): void
    {
        $this->contactDAO->closeContact($contactId, $resolverId);
    }


    /**
     * Retrieves all contacts with matching id
     *
     * @param int $id id
     * @return Contact the matching contact request
     */
    public function getContact(int $id): Contact
    {
        return array_values(array_filter($this->getContactList(), fn($contact) => $contact->getId() === $id))[0];
    }


    /**
     * Retrieves all the contacts requests
     *
     * @return Contact[] list of contacts
     */
    public function getContactList(): array
    {
        return $this->contactDAO->getContactList();
    }
}
