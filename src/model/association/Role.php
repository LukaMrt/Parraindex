<?php

namespace App\model\association;

class Role {

    private string $role;

    public function __construct(string $role) {
        $this->role = $role;
    }

    public function __toString(): string {
        return $this->role;
    }

}