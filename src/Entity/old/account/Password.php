<?php

declare(strict_types=1);

namespace App\Entity\old\account;

class Password
{
    public function __construct(
        private string $password
    ) {
    }

    public function hashPassword(string $algorithm): void
    {
        $this->password = $this->isHashed() ? $this->password : password_hash($this->password, $algorithm);
    }

    public function isHashed(): bool
    {
        return password_get_info($this->password)['algoName'] !== 'unknown';
    }

    public function check(string $passwordConfirm): bool
    {
        return password_verify($this->password, $passwordConfirm);
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function isEmpty(): bool
    {
        return $this->password === '' || $this->password === '0';
    }
}
