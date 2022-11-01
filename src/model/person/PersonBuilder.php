<?php

namespace App\model\person;

use App\model\association\Associations;
use App\model\family\Families;
use App\model\person\characteristic\Characteristics;
use App\model\sponsor\Sponsors;
use App\model\utils\Id;
use App\model\utils\Image;
use DateTime;
use LogicException;

/**
 * Builder instance for {@see Person}.
 */
class PersonBuilder {

    /** @var Id $id */
    private Id $id;

    /** @var Identity $name */
    private Identity $name;

    /** @var DateTime $birthDate */
    private DateTime $birthDate;

    /** @var Biography $biography */
    private Biography $biography;

    /** @var string $picture */
    private string $picture;

    /** @var array $characteristics */
    private array $characteristics;

    /** @var array $sponsors */
    private array $sponsors;

    /** @var array $families */
    private array $families;

    /** @var array $associations */
    private array $associations;

    private function __construct() {
        $this->birthDate = DateTime::createFromFormat('Y-m-d', '0000-00-00');
        $this->biography = Biography::empty();
        $this->picture = null;
        $this->characteristics = array();
        $this->sponsors = array();
        $this->families = array();
        $this->associations = array();
    }

    public static function aPerson(): PersonBuilder {
        return new PersonBuilder();
    }

    /**
     * @param Id $id Set id property.
     * @return $this Builder instance.
     */
    public function withId(Id $id): PersonBuilder {
        $this->id = $id;
        return $this;
    }

    /**
     * @param Identity $name Set name property.
     * @return $this Builder instance.
     */
    public function withName(Identity $name): PersonBuilder {
        $this->name = $name;
        return $this;
    }

    /**
     * @param ?DateTime $birthDate Set birthDate property.
     * @return $this Builder instance.
     */
    public function withBirthDate(?DateTime $birthDate): PersonBuilder {
        $this->birthDate = $birthDate ?? $this->birthDate;
        return $this;
    }

    /**
     * @param ?Biography $biography Set biography property.
     * @return $this Builder instance.
     */
    public function withBiography(?Biography $biography): PersonBuilder {
        $this->biography = $biography ?? $this->biography;
        return $this;
    }

    /**
     * @param ?string $picture Set picture property.
     * @return $this Builder instance.
     */
    public function withPicture(?string $picture): PersonBuilder {
        $this->picture = $picture ?? $this->picture;
        return $this;
    }

    /**
     * @param array $characteristics Set characteristics property.
     * @return $this Builder instance.
     */
    public function withCharacteristics(array $characteristics): PersonBuilder {
        $this->characteristics = $characteristics;
        return $this;
    }

    /**
     * @param array $sponsors Set sponsors property.
     * @return $this Builder instance.
     */
    public function withSponsors(array $sponsors): PersonBuilder {
        $this->sponsors = $sponsors;
        return $this;
    }

    /**
     * @param array $families Set families property.
     * @return $this Builder instance.
     */
    public function withFamilies(array $families): PersonBuilder {
        $this->families = $families;
        return $this;
    }

    /**
     * @param array $associations Set associations property.
     * @return $this Builder instance.
     */
    public function withAssociations(array $associations): PersonBuilder {
        $this->associations = $associations;
        return $this;
    }

    /**
     * @return Person New instance from Builder.
     * @throws LogicException if Builder does not validate.
     */
    public function build(): Person {
        if (!$this->id->isValid() || $this->name->isEmpty()) {
            throw new LogicException(__METHOD__ . ' Called with Incomplete or Invalid Properties');
        }
        return new Person($this);
    }

    /**
     * @return Id
     */
    public function getId(): Id {
        return $this->id;
    }

    /**
     * @return Identity
     */
    public function getName(): Identity {
        return $this->name;
    }

    /**
     * @return DateTime
     */
    public function getBirthDate(): DateTime {
        return $this->birthDate;
    }

    /**
     * @return Biography
     */
    public function getBiography(): Biography {
        return $this->biography;
    }

    /**
     * @return string
     */
    public function getPicture(): string {
        return $this->picture;
    }

    /**
     * @return array
     */
    public function getCharacteristics(): array {
        return $this->characteristics;
    }

    /**
     * @return array
     */
    public function getSponsors(): array {
        return $this->sponsors;
    }

    /**
     * @return array
     */
    public function getFamilies(): array {
        return $this->families;
    }

    /**
     * @return array
     */
    public function getAssociations(): array {
        return $this->associations;
    }

}
