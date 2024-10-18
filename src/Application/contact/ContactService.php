<?php

declare(strict_types=1);

namespace App\Application\contact;

use App\Application\contact\executor\ContactExecutors;
use App\Entity\old\contact\Contact;
use App\Repository\ContactRepository;

class ContactService
{
    public $contactDAO;

    public function __construct(
        private readonly ContactExecutors $contactExecutors,
    ) {
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

        if ($executors === []) {
            return "Le type de contact n'est pas valide.";
        }

        return $executors[0]->execute($parameters);
    }


    /**
     * Close a contact
     *
     * @param int $contactId id of the contact
     * @param int $resolverId id of the person who closed the contact
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
        return array_values(array_filter($this->getContactList(), fn($contact): bool => $contact->getId() === $id))[0];
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
