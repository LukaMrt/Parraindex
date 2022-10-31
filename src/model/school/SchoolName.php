<?php

namespace App\model\school;

class SchoolName {

    private string $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function __toString(): string {
        return $this->name;
    }

}