<?php

namespace App\model\association;

class Associations {

    private array $associations;

    public function __construct(Association... $associations) {
        $this->associations = $associations;
    }

}