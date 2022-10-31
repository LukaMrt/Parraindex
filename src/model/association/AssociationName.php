<?php

namespace App\model\association;

class AssociationName {

    private string $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function __toString(): string {
        return $this->name;
    }

}