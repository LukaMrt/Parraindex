<?php

namespace App\application\contact;

use App\model\contact\DefaultContact;
use App\model\contact\PersonContact;
use App\model\contact\SponsorContact;

interface ContactDAO
{
    public function savePersonAddContact(PersonContact $contact): void;

    public function savePersonRemoveContact(PersonContact $contact): void;

    public function savePersonUpdateContact(PersonContact $contact): void;

    public function saveSimpleContact(DefaultContact $contact): void;

    public function saveChockingContentContact(PersonContact $contact): void;

    public function saveSponsorContact(SponsorContact $contact): void;

    public function getContactList(): array;

    public function closeContact(int $contactId, int $resolverId);
}
