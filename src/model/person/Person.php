<?php

namespace App\model\person;

use App\model\person\characteristic\Characteristic;
use App\model\sponsor\Sponsor;
use JsonSerializable;

/**
 * Person class to represent a person
 */
class Person implements JsonSerializable
{
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
     * @var array Families of the person
     */
    private array $families;
    /**
     * @var array Associations of the person
     */
    private array $associations;
    /**
     * @var array Promotions of the person
     */
    private array $promotions;
    /**
     * @var int Start year of the person
     */
    private int $startYear;


    /**
     * @param PersonBuilder $builder Builder to build the person
     */
    public function __construct(PersonBuilder $builder)
    {
        $this->id = $builder->id;
        $this->identity = $builder->identity;
        $this->biography = $builder->biography;
        $this->color = $builder->color;
        $this->description = $builder->description;
        $this->characteristics = $builder->characteristics;
        $this->sponsors = $builder->sponsors;
        $this->families = $builder->families;
        $this->associations = $builder->associations;
        $this->promotions = $builder->promotions;
        $this->startYear = $builder->startYear;
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

        if (empty($dates)) {
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
    public function addSponsor(array $sponsors): void
    {
        $this->sponsors = array_merge($this->sponsors, $sponsors);
    }


    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
