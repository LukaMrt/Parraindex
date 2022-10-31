<?php

namespace App\model\account;

use App\model\person\Person;
use App\model\utils\Email;
use App\model\utils\Id;

class Account {

    private Id $id;
    private Email $email;
    private Person $user;
    private Privileges $privileges;

    public function __construct(Id $id, Email $email, Person $user) {
        $this->id = $id;
        $this->email = $email;
        $this->user = $user;
    }

}