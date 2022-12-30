<?php

namespace App\model\person;

class Person {

	private int $id;
	private Identity $identity;
	private string $biography;
	private string $color;
	private string $description;
	private array $characteristics;
	private array $sponsors;
	private array $families;
	private array $associations;
	private array $promotions;
    private int $startYear;

	public function __construct(PersonBuilder $builder) {
		$this->id = $builder->getId();
		$this->identity = $builder->getIdentity();
		$this->biography = $builder->getBiography();
		$this->color = $builder->getColor();
		$this->description = $builder->getDescription();
		$this->characteristics = $builder->getCharacteristics();
		$this->sponsors = $builder->getSponsors();
		$this->families = $builder->getFamilies();
		$this->associations = $builder->getAssociations();
		$this->promotions = $builder->getPromotions();
        $this->startYear = $builder->getStartYear();
	}

	/**
	 * @return Identity, an object containing personal information (lastName, fisrtName, photo, birthdate).
	 */
	public function getIdentity(): string {
		return $this->identity;
	}

	/**
	 * @return string the first name of the person.
	 */
	public function getFirstName(): string {
		return $this->identity->getFirstName();
	}

	/**
	 * @return string the last name of the person.
	 */
	public function getLastName(): string {
		return $this->identity->getLastName();
	}

	/**
	 * @return string the short description of the person.
	 */
	public function getBiography(): string {
		return $this->biography;
	}

	/**
	 * @return string The description of the person.
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * @return string The picture URL of the person.
	 */
	public function getPicture(): string {
		return $this->identity->getPicture();
	}

	public function getStartYear(): int|null {

        if (0 <= $this->startYear) {
            return $this->startYear;
        }

		$dates = array_map(fn($promotion) => $promotion->getYear(), $this->promotions);
		
		if(empty($dates)) {
			return null;
		}

		return min($dates);
	}

	/**
	 * @return string, the hex representation of the banner color.
	 */
	public function getColor(): string {
		return $this->color;
	}

	public function getCharacteristics(): array {
		return $this->characteristics;
	}
	
	public function getId(): int {
		return $this->id;
	}
	
	/**
	 * @param int $startYear
	 */
	public function setStartYear(int $startYear): void {
		$this->startYear = $startYear;
	}

	/**
	 * set the new picture URL of the person.
	 * @param string $picture URL of the picture.
	 */
	public function setPicture(string $picture): void {
		$this->identity->setPicture($picture);
	}

}