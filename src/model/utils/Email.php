<?php

namespace App\model\utils;

class Email {

    private string $email;

    public function __construct(string $email) {
        $this->email = $email;
    }

    public function __toString(): string {
        return $this->email;
    }

}