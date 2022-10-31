<?php

namespace App\model\school\promotion;

class Year {

    private int $year;

    public function __construct(int $year) {
        $this->year = $year;
    }

    public function __toString(): string {
        return (string) $this->year;
    }

}