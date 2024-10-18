<?php

declare(strict_types=1);

namespace App\Entity\old\person;

use App\Entity\old\person\characteristic\Characteristic;
use App\Entity\old\sponsor\Sponsor;
use JsonSerializable;

/**
 * Person class to represent a person
 */
class Person implements JsonSerializable
{
    /**
     * @var mixed[]
     */
    public $families;

    /**
     * @var mixed[]
     */
    public $associations;

    /**
     * @var mixed[]
     */
    public $promotions;

    /**
     * @var int Id of the person
     */
    private int $id;

    /**
     * @var Identity Identity of the person
     */
    private Identity $identity;

    /**
     * @var string Biography of the person
     */
    private string $biography;

    /**
     * @var string Color of the person (hexadecimal)
     */
    private string $color;

    /**
     * @var string Description of the person
     */
    private string $description;

    /**
     * @var Characteristic[] Characteristics of the person
     */
    private array $characteristics;

    /**
     * @var Sponsor[] Sponsors of the person
     */
    private array $sponsors;

    /**
     * @var int Start year of the person
     */
    private int $startYear;


    /**
     * @param PersonBuilder $personBuilder Builder to build the person
     */
    public function __construct(PersonBuilder $personBuilder)
    {
        $this->id              = $personBuilder->id;
        $this->identity        = $personBuilder->identity;
        $this->biography       = $personBuilder->biography;
        $this->color           = $personBuilder->color;
        $this->description     = $personBuilder->description;
        $this->characteristics = $personBuilder->characteristics;
        $this->sponsors        = $personBuilder->sponsors;
        $this->families        = $personBuilder->families;
        $this->associations    = $personBuilder->associations;
        $this->promotions      = $personBuilder->promotions;
        $this->startYear       = $personBuilder->startYear;
    }


    /**
     * @return int Id of the person
     */
    public function getId(): int
    {
        return $this->id;
    }


    /**
     * @return Identity Identity of the person
     */
    public function getIdentity(): string
    {
        return $this->identity;
    }


    /**
     * @return string The short description of the person
     */
    public function getBiography(): string
    {
        return $this->biography;
    }


    /**
     * @return string Banner color of the person (hexadecimal)
     */
    public function getColor(): string
    {
        return $this->color;
    }


    /**
     * @return string The description of the person
     */
    public function getDescription(): string
    {
        return $this->description;
    }


    /**
     * @return array Characteristics of the person
     */
    public function getCharacteristics(): array
    {
        return $this->characteristics;
    }


    /**
     * Sets characteristics of the person
     * @param Characteristic[] $characteristics New characteristics of the person
     */
    public function setCharacteristics(array $characteristics): void
    {
        $this->characteristics = $characteristics;
    }


    /**
     * @return int|null Start year of the person
     */
    public function getStartYear(): int|null
    {

        if (0 <= $this->startYear) {
            return $this->startYear;
        }

        $dates = array_map(fn($promotion) => $promotion->getYear(), $this->promotions);

        if ($dates === []) {
            return null;
        }

        return min($dates);
    }


    /**
     * @param int $startYear New start year of the person
     */
    public function setStartYear(int $startYear): void
    {
        $this->startYear = $startYear;
    }


    /**
     * @return string The first name of the person.
     */
    public function getFirstName(): string
    {
        return $this->identity->getFirstName();
    }


    /**
     * @return string The last name of the person.
     */
    public function getLastName(): string
    {
        return $this->identity->getLastName();
    }


    /**
     * @return string The picture URL of the person.
     */
    public function getPicture(): string
    {
        return $this->identity->getPicture();
    }


    /**
     * Sets the picture URL of the person
     * @param string $picture New URL of the picture
     */
    public function setPicture(string $picture): void
    {
        $this->identity->setPicture($picture);
    }


    /**
     * Add Sponsors to the person
     * @param array $sponsors New sponsors of the person
     */
    public function addSponsors(array $sponsors): void
    {
        $this->sponsors = array_merge($this->sponsors, $sponsors);
    }


    #[\Override]
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
