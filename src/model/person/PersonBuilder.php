<?php

namespace App\model\person;

use DateTime;
use LogicException;

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


	/** @var array $characteristics */
	private array $characteristics;

	/** @var array $sponsors */
	private array $sponsors;

	/** @var array $families */
	private array $families;

	/** @var array $associations */
	private array $associations;

	private function __construct() {
		$this->biography = '';
		$this->characteristics = array();
		$this->sponsors = array();
		$this->families = array();
		$this->associations = array();
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
     * @param string|null $biography Set biography property.
     * @return $this Builder instance.
     */
	public function withBiography(?string $biography): PersonBuilder {
		$this->biography = $biography ?? $this->biography;
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
		if ($this->identity->isEmpty()) {
			throw new LogicException(__METHOD__ . ' Called with Incomplete or Invalid Properties');
		}
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