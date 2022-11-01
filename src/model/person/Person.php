<?php

namespace App\model\person;

use App\model\association\Associations;
use App\model\family\Families;
use App\model\person\characteristic\Characteristics;
use App\model\sponsor\Sponsors;
use App\model\utils\Id;
use App\model\utils\Image;
use DateTime;

class Person {

	private Id $id;
	private Identity $name;
	private DateTime $birthDate;
	private Biography $biography;
	private string $picture;
	private array $characteristics;
	private array $sponsors;
	private array $families;
	private array $associations;

	public function __construct(PersonBuilder $builder) {
		$this->id = $builder->getId();
		$this->name = $builder->getName();
		$this->birthDate = $builder->getBirthDate();
		$this->biography = $builder->getBiography();
		$this->picture = $builder->getPicture();
		$this->characteristics = $builder->getCharacteristics();
		$this->sponsors = $builder->getSponsors();
		$this->families = $builder->getFamilies();
		$this->associations = $builder->getAssociations();
	}

	public function getName(): string {
		return $this->name;
	}

	public function getFirstName(): string {
		return $this->name->getFirstName();
	}

	public function getLastName(): string {
		return $this->name->getLastName();
	}

}