<?php

namespace App\model\school\degree;

class Ects {

    private int $ects;

    public function __construct(int $ects) {
        $this->ects = $ects;
    }

    public function __toString(): string {
        return (string) $this->ects;
    }

}