<?php

namespace App\model\person;

use DateTime;

class Person {

	private int $id;
	private Identity $identity;
	private string $biography;
	private array $characteristics;
	private array $sponsors;
	private array $families;
	private array $associations;

	public function __construct(PersonBuilder $builder) {
		$this->id = $builder->getId();
		$this->identity = $builder->getIdentity();
		$this->biography = $builder->getBiography();
		$this->characteristics = $builder->getCharacteristics();
		$this->sponsors = $builder->getSponsors();
		$this->families = $builder->getFamilies();
		$this->associations = $builder->getAssociations();
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

}