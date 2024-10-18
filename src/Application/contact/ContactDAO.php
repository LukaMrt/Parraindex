<?php

declare(strict_types=1);

namespace App\Application\contact;

use App\Entity\old\contact\Contact;
use App\Entity\old\contact\DefaultContact;
use App\Entity\old\contact\PersonContact;
use App\Entity\old\contact\SponsorContact;

/**
 * Class ContactDAO
 * Manage the contact data access and manipulation
 * @see Contact
 */
interface ContactDAO
{
    /**
     * Saves a contact representing the adding of a new person
     * @param PersonContact $personContact the contact to save
     */
    public function savePersonAddContact(PersonContact $personContact): void;


    /**
     * Saves a contact representing the suppression of a person
     * @param PersonContact $personContact the contact to remove
     */
    public function savePersonRemoveContact(PersonContact $personContact): void;


    /**
     * Saves a contact representing the modification of a person
     * @param PersonContact $personContact the contact to save
     */
    public function savePersonUpdateContact(PersonContact $personContact): void;


    /**
     * Saves a contact representing a simple message
     * @param DefaultContact $defaultContact the contact to save
     */
    public function saveSimpleContact(DefaultContact $defaultContact): void;


    /**
     * Saves a contact representing a message related to a chocking content
     * @param PersonContact $personContact the contact to save
     */
    public function saveChockingContentContact(PersonContact $personContact): void;


    /**
     * Saves a contact representing a message related to the manipulation of a sponsor
     * @param SponsorContact $sponsorContact the contact to save
     */
    public function saveSponsorContact(SponsorContact $sponsorContact): void;


    /**
     * Retrieves all the saved contacts (related to a person, a sponsor or to a simple message)
     * @return array the list of all the contacts
     */
    public function getContactList(): array;


    /**
     * Closes a contact by adding the date of the closing and the person who closed it
     * @param int $contactId the id of the contact to close
     * @param int $resolverId the id of the person who closed the contact
     */
    public function closeContact(int $contactId, int $resolverId): void;
}
