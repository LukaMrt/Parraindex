<?php

declare(strict_types=1);

namespace App\Entity\old\person;

use DateTime;
use JsonSerializable;

/**
 * Identity of a person
 */
class Identity implements JsonSerializable
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
     * @param string $firstName First name of the person
     * @param string $lastName Last name of the person
     * @param string|null $picture Profile picture of the person
     */
    public function __construct(string $firstName, string $lastName, ?string $picture = null)
    {
        $this->firstName = ucwords(strtolower($firstName));
        $this->lastName  = ucwords(strtolower($lastName));
        $this->picture   = $picture ?? 'no-picture.svg';
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
    #[\Override]
    public function __toString(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }


    /**
     * @return bool True if the identity is empty, false otherwise
     */
    public function isEmpty(): bool
    {
        return ($this->firstName === '' || $this->firstName === '0') && ($this->lastName === '' || $this->lastName === '0');
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
     */
    public function setPicture(string $picture): void
    {
        $this->picture = $picture;
    }


    #[\Override]
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
