<?php

namespace App\model\family;

class FamilyName {

    private string $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function __toString() {
        return $this->name;
    }

}