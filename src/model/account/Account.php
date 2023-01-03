<?php

namespace App\model\account;

use App\model\person\Person;

class Account
{

    private int $id;
    private string $email;
    private Person $user;
    private Password $password;
    private array $privileges;


    public function __construct(int $id, string $email, Person $user, Password $password, Privilege ...$privileges)
    {
        $this->id = $id;
        $this->email = $email;
        $this->user = $user;
        $this->password = $password;
        $this->privileges = $privileges;
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


    public function getHighestPrivilege(): PrivilegeType
    {
        $highest = PrivilegeType::STUDENT;

        foreach ($this->privileges as $privilege) {
            if ($privilege->isHigherThan($highest)) {
                $highest = $privilege->getPrivilegeType();
            }
        }

        return $highest;
    }

}
