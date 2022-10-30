<?php

namespace App\model\family;

use App\model\user\User;
use App\model\utils\Id;

class Family {

    private Id $id;
    private FamilyName $name;
    private User $creator;
    private array $members;

    public function __construct(Id $id, FamilyName $name, User $creator, User... $members) {
        $this->id = $id;
        $this->name = $name;
        $this->creator = $creator;
        $this->members = $members;
    }

}