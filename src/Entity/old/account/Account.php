<?php

namespace App\Entity\account;

use App\Entity\person\Person;

/**
 * Account class representing a signed up person
 */
class Account
{
    /**
     * @var int id of the account
     */
    private int $id;
    /**
     * @var string email of the account
     */
    private string $email;
    /**
     * @var Person person related to the account
     */
    private Person $user;
    /**
     * @var Password password of the account
     */
    private Password $password;
    /**
     * @var Privilege[] privileges of the account
     */
    private array $privileges;


    /**
     * @param int $id id of the account
     * @param string $email email of the account
     * @param Person $user person related to the account
     * @param Password $password password of the account
     * @param Privilege ...$privileges privileges of the account
     */
    public function __construct(int $id, string $email, Person $user, Password $password, Privilege ...$privileges)
    {
        $this->id = $id;
        $this->email = $email;
        $this->user = $user;
        $this->password = $password;
        $this->privileges = $privileges;
    }


    /**
     * @return string email of the account
     */
    public function getLogin(): string
    {
        return $this->email;
    }


    /**
     * @return string password of the account, hashed if it is not already hashed
     */
    public function getHashedPassword(): string
    {
        if (!$this->password->isHashed()) {
            $this->password->hashPassword(PASSWORD_DEFAULT);
        }
        return $this->password->getPassword();
    }


    /**
     * @return int id of the person related to the account
     */
    public function getPersonId(): int
    {
        return $this->user->getId();
    }


    /**
     * @return int id of the account
     */
    public function getId(): int
    {
        return $this->id;
    }


    /**
     * Analyzes the privileges of the account and returns the highest privilege
     * @return PrivilegeType highest privilege of the account
     */
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
