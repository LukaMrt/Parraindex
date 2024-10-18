<?php

declare(strict_types=1);

namespace App\Entity\old\person;

use App\Entity\old\person\characteristic\Characteristic;
use App\Entity\old\school\promotion\Promotion;
use App\Entity\Person\Person;
use DateTime;

/**
 * Builder instance for {@see Person}.
 */
class PersonBuilder
{
    /**
     * @var int The id of the person
     */
    public int $id = 0;

    /**
     * @var Identity The identity of the person
     */
    public Identity $identity;

    /**
     * @var DateTime The birthdate of the person
     */
    public DateTime $birthDate;

    /**
     * @var string The biography of the person
     */
    public string $biography = '';

    /**
     * @var string The description of the person
     */
    public string $description = '';

    /**
     * @var array The characteristics of the person
     */
    public array $characteristics = [];

    /**
     * @var array The sponsors of the person
     */
    public array $sponsors = [];

    /**
     * @var array The families of the person
     */
    public array $families = [];

    /**
     * @var array The associations of the person
     */
    public array $associations = [];

    /**
     * @var string The color of the person (hexadecimal)
     */
    public string $color = '#f0f0f0';

    /**
     * @var array The promotions of the person
     */
    public array $promotions = [];

    /**
     * @var int The year when the person started at the IUT
     */
    public int $startYear = 0;


    /**
     * Private constructor. Use {@see PersonBuilder::create()} instead.
     */
    private function __construct()
    {
        $this->identity        = new Identity('', '');
        $this->birthDate       = new DateTime();
    }


    /**
     * @return PersonBuilder A new instance of PersonBuilder.
     */
    public static function aPerson(): PersonBuilder
    {
        return new PersonBuilder();
    }


    /**
     * Sets the id of the person
     * @param int $id The id of the person
     * @return $this The builder instance for chaining
     */
    public function withId(int $id): PersonBuilder
    {
        $this->id = $id;
        return $this;
    }


    /**
     * Sets the identity of the person
     * @param Identity $identity The identity of the person
     * @return PersonBuilder The builder instance for chaining
     */
    public function withIdentity(Identity $identity): PersonBuilder
    {
        $this->identity = $identity;
        return $this;
    }


    /**
     * Sets the banner color of the person
     * @param string|null $color The banner color of the person (hexadecimal)
     * @return PersonBuilder The builder instance for chaining
     */
    public function withColor(?string $color): PersonBuilder
    {
        $this->color = $color ?? $this->color;
        return $this;
    }


    /**
     * Sets the biography of the person
     * @param string|null $biography The biography of the person
     * @return PersonBuilder The builder instance for chaining
     */
    public function withBiography(?string $biography): PersonBuilder
    {
        $this->biography = $biography ?? $this->biography;
        return $this;
    }


    /**
     * Sets the description of the person
     * @param string|null $description The description of the person
     * @return PersonBuilder The builder instance for chaining
     */
    public function withDescription(?string $description): PersonBuilder
    {
        $this->description = $description ?? $this->description;
        return $this;
    }


    /**
     * Sets the characteristics of the person
     * @param array $characteristics The characteristics of the person
     * @return PersonBuilder The builder instance for chaining
     */
    public function withCharacteristics(array $characteristics): PersonBuilder
    {
        $this->characteristics = $characteristics;
        return $this;
    }


    /**
     * Adds a characteristic to the person
     * @param Characteristic $characteristic The characteristic to add
     * @return PersonBuilder The builder instance for chaining
     */
    public function addCharacteristic(Characteristic $characteristic): PersonBuilder
    {
        if (!in_Array($characteristic, $this->characteristics)) {
            $this->characteristics[] = $characteristic;
        }

        return $this;
    }


    /**
     * Sets the start year of the person
     * @param int $startYear The year when the person started at the IUT
     * @return PersonBuilder The builder instance for chaining
     */
    public function withStartYear(int $startYear): PersonBuilder
    {
        $this->startYear = $startYear;
        return $this;
    }


    /**
     * Adds a promotion to the person
     * @param Promotion $promotion The promotion to add
     * @return PersonBuilder The builder instance for chaining
     */
    public function addPromotion(Promotion $promotion): PersonBuilder
    {
        $this->promotions[] = $promotion;
        return $this;
    }


    /**
     * Build the {@see Person} instance from the builder data
     * @return Person The built person
     */
    public function build(): Person
    {
        return new Person($this);
    }
}
