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

    /** @var Names $name */
    private Names $name;

    /** @var DateTime $birthDate */
    private DateTime $birthDate;

    /** @var Biography $biography */
    private Biography $biography;

    /** @var Image $picture */
    private Image $picture;

    /** @var Characteristics $characteristics */
    private Characteristics $characteristics;

    /** @var Sponsors $sponsors */
    private Sponsors $sponsors;

    /** @var Families $families */
    private Families $families;

    /** @var Associations $associations */
    private Associations $associations;

    private function __construct() {
        $this->birthDate = DateTime::createFromFormat('Y-m-d', '0000-00-00');
        $this->biography = Biography::empty();
        $this->picture = Image::empty();
        $this->characteristics = Characteristics::empty();
        $this->sponsors = Sponsors::empty();
        $this->families = Families::empty();
        $this->associations = Associations::empty();
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
     * @param Names $name Set name property.
     * @return $this Builder instance.
     */
    public function withName(Names $name): PersonBuilder {
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
     * @param ?Image $picture Set picture property.
     * @return $this Builder instance.
     */
    public function withPicture(?Image $picture): PersonBuilder {
        $this->picture = $picture ?? $this->picture;
        return $this;
    }

    /**
     * @param Characteristics $characteristics Set characteristics property.
     * @return $this Builder instance.
     */
    public function withCharacteristics(Characteristics $characteristics): PersonBuilder {
        $this->characteristics = $characteristics;
        return $this;
    }

    /**
     * @param Sponsors $sponsors Set sponsors property.
     * @return $this Builder instance.
     */
    public function withSponsors(Sponsors $sponsors): PersonBuilder {
        $this->sponsors = $sponsors;
        return $this;
    }

    /**
     * @param Families $families Set families property.
     * @return $this Builder instance.
     */
    public function withFamilies(Families $families): PersonBuilder {
        $this->families = $families;
        return $this;
    }

    /**
     * @param Associations $associations Set associations property.
     * @return $this Builder instance.
     */
    public function withAssociations(Associations $associations): PersonBuilder {
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
     * @return Names
     */
    public function getName(): Names {
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
     * @return Image
     */
    public function getPicture(): Image {
        return $this->picture;
    }

    /**
     * @return Characteristics
     */
    public function getCharacteristics(): Characteristics {
        return $this->characteristics;
    }

    /**
     * @return Sponsors
     */
    public function getSponsors(): Sponsors {
        return $this->sponsors;
    }

    /**
     * @return Families
     */
    public function getFamilies(): Families {
        return $this->families;
    }

    /**
     * @return Associations
     */
    public function getAssociations(): Associations {
        return $this->associations;
    }

}
