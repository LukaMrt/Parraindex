<?php

namespace App\application\contact;

use App\model\contact\Contact;

interface ContactDAO {

	public function saveContact(Contact $contact): void;

}