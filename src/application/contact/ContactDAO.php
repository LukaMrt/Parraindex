<?php

namespace App\application\contact;

use App\model\contact\Contact;
use App\model\contact\DefaultContact;
use App\model\contact\PersonContact;
use App\model\contact\SponsorContact;

/**
 * Class ContactDAO
 * Manage the contact data access and manipulation
 * @see Contact
 */
interface ContactDAO
{


    /**
     * Saves a contact representing the adding of a new person
     * @param PersonContact $contact the contact to save
     * @return void
     */
    public function savePersonAddContact(PersonContact $contact): void;


    /**
     * Saves a contact representing the suppression of a person
     * @param PersonContact $contact the contact to remove
     * @return void
     */
    public function savePersonRemoveContact(PersonContact $contact): void;


    /**
     * Saves a contact representing the modification of a person
     * @param PersonContact $contact the contact to save
     * @return void
     */
    public function savePersonUpdateContact(PersonContact $contact): void;


    /**
     * Saves a contact representing a simple message
     * @param DefaultContact $contact the contact to save
     * @return void
     */
    public function saveSimpleContact(DefaultContact $contact): void;


    /**
     * Saves a contact representing a message related to a chocking content
     * @param PersonContact $contact the contact to save
     * @return void
     */
    public function saveChockingContentContact(PersonContact $contact): void;


    /**
     * Saves a contact representing a message related to the manipulation of a sponsor
     * @param SponsorContact $contact the contact to save
     * @return void
     */
    public function saveSponsorContact(SponsorContact $contact): void;


    /**
     * Retrieves all the saved contacts (related to a person, a sponsor or to a simple message)
     * @return array the list of all the contacts
     */
    public function getContactList(): array;


    /**
     * Closes a contact by adding the date of the closing and the person who closed it
     * @param int $contactId the id of the contact to close
     * @param int $resolverId the id of the person who closed the contact
     * @return void
     */
    public function closeContact(int $contactId, int $resolverId): void;

}
