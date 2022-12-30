<?php

namespace App\model\person;

class Person {

	private int $id;
	private Identity $identity;
	private string $biography;
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
		$this->characteristics = $builder->getCharacteristics();
		$this->sponsors = $builder->getSponsors();
		$this->families = $builder->getFamilies();
		$this->associations = $builder->getAssociations();
		$this->promotions = $builder->getPromotions();
        $this->startYear = $builder->getStartYear();
	}

	public function getIdentity(): string {
		return $this->identity;
	}

	public function getFirstName(): string {
		return $this->identity->getFirstName();
	}

	public function getLastName(): string {
		return $this->identity->getLastName();
	}

	public function getBiography(): string {
		return $this->biography;
	}

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
     * @param int $startYear
     */
    public function setStartYear(int $startYear): void {
        $this->startYear = $startYear;
    }

	public function getCharacteristics(): array {
		return $this->characteristics;
	}

	public function getId(): int {
		return $this->id;
	}

}