<?php

namespace App\model\account;

class Password {

    private string $password;

    public function __construct(string $password) {
        $this->password = $password;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function hashPassword($algorithm): void {
        $this->password = password_hash($this->password, $algorithm);
    }

    public function check($passwordConfirm): bool {
        return password_verify($this->getPassword(), $passwordConfirm);
    }

    public function isEmpty(): bool {
        return empty($this->password);
    }

}