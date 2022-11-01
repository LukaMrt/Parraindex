<?php

namespace App\model\account;

use App\model\person\Person;
use App\model\utils\Id;

class Account {

    private Id $id;
    private string $email;
    private Person $user;
    private array $privileges;

    public function __construct(Id $id, string $email, Person $user, Privilege... $privileges) {
        $this->id = $id;
        $this->email = $email;
        $this->user = $user;
		$this->privileges = $privileges;
    }

}