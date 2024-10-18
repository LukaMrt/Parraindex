<?php

namespace App\Entity\old\account;

use App\Entity\old\person\Person;
use App\Entity\Person\Role;

/**
 * Account class representing a signed up person
 */
readonly class Account
{
    public function __construct(
        private int $id,
        private string $email,
        private Person $user,
        private Password $password,
        private Role $role,
    ) {
    }

    public function getLogin(): string
    {
        return $this->email;
    }

    public function getHashedPassword(): string
    {
        if (!$this->password->isHashed()) {
            $this->password->hashPassword(PASSWORD_DEFAULT);
        }
        return $this->password->getPassword();
    }

    public function getPersonId(): int
    {
        return $this->user->getId();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRole(): Role
    {
        return $this->role;
    }
}
