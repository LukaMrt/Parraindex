<?php

namespace App\model\user;

use DateTime;
use LogicException;

/**
 * Builder instance for {@see User}.
 */
class UserBuilder {

    /** @var int $id */
    private int $id = -1;

    /** @var string $lastName */
    private string $lastName = "";

    /** @var string $firstName */
    private string $firstName = "";

    /** @var DateTime $birthDate */
    private DateTime $birthDate;

    /** @var string $biography */
    private string $biography = "";

    /** @var string $picture */
    private string $picture = "";

    public function __construct() {
        $this->birthDate = DateTime::createFromFormat('d-m-Y', '01-01-1970');
    }

    /**
     * @param int $id Set id property.
     * @return $this Builder instance.
     */
    public function withId(int $id): UserBuilder {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $lastName Set lastName property.
     * @return $this Builder instance.
     */
    public function withLastName(string $lastName): UserBuilder {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @param string $firstName Set firstName property.
     * @return $this Builder instance.
     */
    public function withFirstName(string $firstName): UserBuilder {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @param string $birthDate Set birthDate property.
     * @return $this Builder instance.
     */
    public function withBirthDate(string $birthDate): UserBuilder {
        $this->birthDate = DateTime::createFromFormat('d/m/Y', $birthDate);
        return $this;
    }

    /**
     * @param string $biography Set biography property.
     * @return $this Builder instance.
     */
    public function withBiography(string $biography): UserBuilder {
        $this->biography = $biography;
        return $this;
    }

    /**
     * @param string $picture Set picture property.
     * @return $this Builder instance.
     */
    public function withPicture(string $picture): UserBuilder {
        $this->picture = $picture;
        return $this;
    }

    /**
     * @return User New instance from Builder.
     * @throws LogicException if Builder does not validate.
     */
    public function build(): User {
        if (!$this->isValid()) {
            throw new LogicException("Builder is not valid.");
        }
        return new User($this);
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLastName(): string {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getFirstName(): string {
        return $this->firstName;
    }

    /**
     * @return DateTime
     */
    public function getBirthDate(): DateTime {
        return $this->birthDate;
    }

    /**
     * @return string
     */
    public function getBiography(): string {
        return $this->biography;
    }

    /**
     * @return string
     */
    public function getPicture(): string {
        return $this->picture;
    }

    /**
     * Validate the builder instance.
     * @return true if the builders properties are valid, false if not.
     */
    private function isValid(): bool {
        return $this->id >= 0 && !empty($this->lastName) && !empty($this->firstName);
    }

}
