<?php

namespace App\model\school\degree;

class DegreeLevel {

    private int $level;

    public function __construct(int $level) {
        $this->level = $level;
    }

    public function __toString(): string {
        return (string) $this->level;
    }

}