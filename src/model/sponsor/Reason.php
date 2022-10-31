<?php

namespace App\model\sponsor;

class Reason {

    private string $reason;

    public function __construct(string $reason) {
        $this->reason = $reason;
    }

    public function __toString(): string {
        return $this->reason;
    }

}