<?php

namespace App\model\person;

use DateTime;

/**
 * Identity of a person
 */
class Identity
{
    /**
     * @var string First name of the person
     */
    private string $firstName;
    /**
     * @var string Last name of the person
     */
    private string $lastName;
    /**
     * @var string|null Profile picture of the person
     */
    private ?string $picture;
    /**
     * @var DateTime|null Birthdate of the person
     */
    private ?DateTime $birthdate;


    /**
     * @param string $firstName First name of the person
     * @param string $lastName Last name of the person
     * @param string|null $picture Profile picture of the person
     * @param string|null $birthdate Birthdate of the person
     */
    public function __construct(string $firstName, string $lastName, ?string $picture = null, ?string $birthdate = null)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->picture = $picture ?? 'no-picture.svg';

        if ($birthdate) {
            $this->birthdate = DateTime::createFromFormat("Y-m-d", $birthdate);
        }
    }


    /**
     * @return Identity Default empty identity
     */
    public static function default(): Identity
    {
        return new Identity("", "", null, null);
    }


    /**
     * @return string Simple representation of the identity (first name + last name)
     */
    public function __toString(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }


    /**
     * @return bool True if the identity is empty, false otherwise
     */
    public function isEmpty(): bool
    {
        return empty($this->firstName) && empty($this->lastName);
    }


    /**
     * @return string First name of the person
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }


    /**
     * @return string Last name of the person
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }


    /**
     * @return string Profile picture of the person
     */
    public function getPicture(): string
    {
        return $this->picture;
    }


    /**
     * @param string $picture New profile picture of the person
     * @return void
     */
    public function setPicture(string $picture): void
    {
        $this->picture = $picture;
    }
}
