<?php

namespace App\model\person;

use App\model\person\characteristic\Characteristic;
use App\model\school\promotion\Promotion;
use DateTime;

/**
 * Builder instance for {@see Person}.
 */
class PersonBuilder
{

    /** @var int $id */
    public int $id;

    /** @var Identity $name */
    public Identity $identity;

    /** @var DateTime $birthDate */
    public DateTime $birthDate;

    /** @var string $biography */
    public string $biography;

    /** @var string $description */
    public string $description;

    /** @var array $characteristics */
    public array $characteristics;

    /** @var array $sponsors */
    public array $sponsors;

    /** @var array $families */
    public array $families;

    /** @var array $associations */
    public array $associations;

    /** @var string $color , hex representation of the banner color. */
    public string $color;

    public array $promotions;

    public int $startYear;


    private function __construct()
    {
        $this->id = 0;
        $this->identity = new Identity('', '');
        $this->birthDate = new DateTime();
        $this->biography = '';
        $this->description = '';
        $this->characteristics = array();
        $this->sponsors = array();
        $this->families = array();
        $this->associations = array();
        $this->promotions = array();
        $this->startYear = 0;
        $this->color = '#f0f0f0';
    }


    public static function aPerson(): PersonBuilder
    {
        return new PersonBuilder();
    }


    /**
     * @param int $id Set id property.
     * @return $this Builder instance.
     */
    public function withId(int $id): PersonBuilder
    {
        $this->id = $id;
        return $this;
    }


    /**
     * @param Identity $identity Set name property.
     * @return $this Builder instance.
     */
    public function withIdentity(Identity $identity): PersonBuilder
    {
        $this->identity = $identity;
        return $this;
    }


    /**
     * @param string|null $color Set banner color property.
     * @return $this Builder instance.
     */
    public function withColor(?string $color): PersonBuilder
    {
        $this->color = $color ?? $this->color;
        return $this;
    }


    /**
     * @param string|null $biography Set biography property.
     * @return $this Builder instance.
     */
    public function withBiography(?string $biography): PersonBuilder
    {
        $this->biography = $biography ?? $this->biography;
        return $this;
    }


    /**
     * @param string|null $description Set description property.
     * @return $this Builder instance.
     */
    public function withDescription(?string $description): PersonBuilder
    {
        $this->description = $description ?? $this->description;
        return $this;
    }


    /**
     * @param array $characteristics Set characteristics property.
     * @return $this Builder instance.
     */
    public function withCharacteristics(array $characteristics): PersonBuilder
    {
        $this->characteristics = $characteristics;
        return $this;
    }


    /**
     * @param Characteristic $characteristic Add a characteristic to the person.
     * @return $this Builder instance.
     */
    public function addCharacteristic(Characteristic $characteristic): PersonBuilder
    {
        if (!in_Array($characteristic, $this->characteristics)) {
            $this->characteristics[] = $characteristic;
        }
        return $this;
    }


    /**
     * @param int $startYear Set the entry year of the person.
     * @return $this Builder instance.
     */
    public function withStartYear(int $startYear): PersonBuilder
    {
        $this->startYear = $startYear;
        return $this;
    }


    /**
     * @param Promotion $promotion Add a promotion to the person.
     */
    public function addPromotion(Promotion $promotion): PersonBuilder
    {
        $this->promotions[] = $promotion;
        return $this;
    }


    /**
     * @return Person New instance from Builder.
     */
    public function build(): Person
    {
        return new Person($this);
    }

}
