<?php

namespace App\model\family;

class Families {

    private array $families;

    public function __construct(Families... $families) {
        $this->families = $families;
    }

}