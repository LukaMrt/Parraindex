<?php

namespace App\model\school\degree;

class Duration {

    private int $years;

    public function __construct(int $years) {
        $this->years = $years;
    }

    public function __toString(): string {
        return $this->years . ' years';
    }

}
