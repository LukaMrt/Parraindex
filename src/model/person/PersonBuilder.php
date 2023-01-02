<?php

namespace App\model\person;

use App\model\person\characteristic\Characteristic;
use App\model\school\promotion\Promotion;
use DateTime;

/**
 * Builder instance for {@see Person}.
 */
class PersonBuilder {

	/** @var int $id */
	private int $id;

	/** @var Identity $name */
	private Identity $identity;

	/** @var DateTime $birthDate */
	private DateTime $birthDate;

	/** @var string $biography */
	private string $biography;

	/** @var string $description */
	private string $description;

	/** @var array $characteristics */
	private array $characteristics;

	/** @var array $sponsors */
	private array $sponsors;

	/** @var array $families */
	private array $families;

	/** @var array $associations */
	private array $associations;

	/** @var string $color, hex representation of the banner color. */
	private string $color;

	private array $promotions;
    private int $startYear;

    private function __construct() {
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

	public static function aPerson(): PersonBuilder {
		return new PersonBuilder();
	}

    /**
	 * @param int $id Set id property.
	 * @return $this Builder instance.
	 */
	public function withId(int $id): PersonBuilder {
		$this->id = $id;
		return $this;
	}

	/**
	 * @param Identity $identity Set name property.
	 * @return $this Builder instance.
	 */
	public function withIdentity(Identity $identity): PersonBuilder {
		$this->identity = $identity;
		return $this;
	}


	/**
	 * @param string|null $color Set banner color property.
	 * @return $this Builder instance.
	 */
	public function withColor(?string $color): PersonBuilder {
		$this->color = $color ?? $this->color;
		return $this;
	}

    /**
     * @param string|null $biography Set biography property.
     * @return $this Builder instance.
     */
	public function withBiography(?string $biography): PersonBuilder {
		$this->biography = $biography ?? $this->biography;
		return $this;
	}

	/**
	 * @param string|null $description Set description property.
	 * @return $this Builder instance.
	 */
	public function withDescription(?string $description): PersonBuilder {
		$this->description = $description ?? $this->description;
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
	 * @param Characteristic $characteristic Add a characteristic to the person.
	 * @return $this Builder instance.
	 */
	public function addCharacteristic(Characteristic $characteristic): PersonBuilder {
		if (!in_Array($characteristic, $this->characteristics)) {
			$this->characteristics[] = $characteristic;
		}
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
	 * @param int $startYear Set the entry year of the person.
	 * @return $this Builder instance.
	 */
    public function withStartYear(int $startYear): PersonBuilder {
        $this->startYear = $startYear;
        return $this;
    }

	/**
	 * @param array $promotions Set promotions property.
	 * @return $this Builder instance.
	 */
    public function withPromotions(array $promotions): PersonBuilder {
		$this->promotions = $promotions;
		return $this;
	}

	/**
	 * @param Promotion $promotion Add a promotion to the person.
	 */
    public function addPromotion(Promotion $promotion): PersonBuilder {
		$this->promotions[] = $promotion;
		return $this;
	}

    /**
	 * @return Person New instance from Builder.
	 */
	public function build(): Person {
		return new Person($this);
	}

    /**
     * @return int
     */
	public function getId(): int {
		return $this->id;
	}

    /**
	 * @return Identity
	 */
	public function getIdentity(): Identity {
		return $this->identity;
	}

    /**
	 * @return string
	 */
	public function getBiography(): string {
		return $this->biography;
	}

	/**
	 * @return string, the hex representation of the banner-color.
	 */
	public function getColor(): string {
		return $this->color;
	}

	/**
	 * @return string The description of the person.
	 */
	public function getDescription(): string {
		return $this->description;
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

    public function getPromotions(): array {
		return $this->promotions;
	}

    /**
     * @return DateTime
     */
    public function getBirthDate(): DateTime {
        return $this->birthDate;
    }

    /**
     * @return int
     */
    public function getStartYear(): int {
        return $this->startYear;
    }

}