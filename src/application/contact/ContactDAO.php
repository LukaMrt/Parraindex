<?php

namespace App\application\contact;

use App\model\contact\Contact;
use App\model\person\Person;
use App\model\sponsor\Sponsor;

interface ContactDAO {

	public function savePersonAddContact(Person $person, Contact $contact): void;

	public function savePersonRemoveContact(?Person $person, Contact $contact): void;

	public function savePersonUpdateContact(?Person $person, Contact $contact): void;

	public function saveSimpleContact(Contact $contact): void;

	public function saveChockingContentContact(Person $person, Contact $contact): void;

	public function saveSponsorContact(Contact $contact, Sponsor $sponsor): void;

}