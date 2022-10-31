<?php

namespace App\model\association;

class Siret {

    private int $siret;

    public function __construct(int $siret) {
        $this->siret = $siret;
    }

    public function __toString(): string {
        return (string)$this->siret;
    }

}