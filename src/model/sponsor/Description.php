<?php

namespace App\model\sponsor;

class Description {

    private string $description;

    public function __construct(string $reason) {
        $this->description = $reason;
    }

    public function __toString(): string {
        return $this->description;
    }

}