<?php

namespace App\model\account;

class Privileges {

    private array $privileges;

    public function __construct(Privilege... $privileges) {
        $this->privileges = $privileges;
    }

}