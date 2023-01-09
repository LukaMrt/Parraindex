<?php

namespace App\model\account;

/**
 * Password of an account
 */
class Password
{
    /**
     * @var string value of the password
     */
    private string $password;


    /**
     * @param string $password value of the password
     */
    public function __construct(string $password)
    {
        $this->password = $password;
    }


    /**
     * Hash the password using the given algorithm
     * @param string $algorithm algorithm to use
     * @return void
     */
    public function hashPassword(string $algorithm): void
    {
        $this->password = $this->isHashed() ? $this->password : password_hash($this->password, $algorithm);
    }


    /**
     * Verify if the given password matches the stored password
     * @param string $passwordConfirm password to verify
     * @return bool true if the password matches, false otherwise
     */
    public function check(string $passwordConfirm): bool
    {
        return password_verify($this->getPassword(), $passwordConfirm);
    }


    /**
     * @return string value of the password
     */
    public function getPassword(): string
    {
        return $this->password;
    }


    /**
     * Verifies if the password is empty
     * @return bool true if the password is empty, false otherwise
     */
    public function isEmpty(): bool
    {
        return empty($this->password);
    }


    /**
     * Verifies if the password is already hashed
     * @return bool true if the password is already hashed, false otherwise
     */
    public function isHashed(): bool
    {
        return password_get_info($this->password)['algoName'] !== 'unknown';
    }
}
