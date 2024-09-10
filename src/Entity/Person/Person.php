<?php

namespace App\Entity\Person;

use App\Entity\Characteristic\Characteristic;
use App\Entity\Sponsor\Sponsor;
use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $biography = null;

    #[ORM\Column(length: 255)]
    private ?string $color = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $startYear = null;

    /**
     * @var Collection<int, Sponsor>
     */
    #[ORM\OneToMany(targetEntity: Sponsor::class, mappedBy: 'godFather', orphanRemoval: true)]
    private Collection $godFathers;

    /**
     * @var Collection<int, Sponsor>
     */
    #[ORM\OneToMany(targetEntity: Sponsor::class, mappedBy: 'godChild', orphanRemoval: true)]
    private Collection $godChildren;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @var Collection<int, Characteristic>
     */
    #[ORM\OneToMany(targetEntity: Characteristic::class, mappedBy: 'person', orphanRemoval: true)]
    private Collection $characteristics;

    public function __construct()
    {
        $this->sponsors = new ArrayCollection();
        $this->godFathers = new ArrayCollection();
        $this->godChildren = new ArrayCollection();
        $this->characteristics = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): static
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): static
    {
        $this->biography = $biography;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStartYear(): ?int
    {
        return $this->startYear;
    }

    public function setStartYear(int $startYear): static
    {
        $this->startYear = $startYear;

        return $this;
    }

    /**
     * @return Collection<int, Sponsor>
     */
    public function getGodFathers(): Collection
    {
        return $this->godFathers;
    }

    public function addGodFather(Sponsor $godFather): static
    {
        if (!$this->godFathers->contains($godFather)) {
            $this->godFathers->add($godFather);
            $godFather->setGodFather($this);
        }

        return $this;
    }

    public function removeGodFather(Sponsor $godFather): static
    {
        if ($this->godFathers->removeElement($godFather)) {
            // set the owning side to null (unless already changed)
            if ($godFather->getGodFather() === $this) {
                $godFather->setGodFather(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sponsor>
     */
    public function getGodChildren(): Collection
    {
        return $this->godChildren;
    }

    public function addGodChild(Sponsor $godChild): static
    {
        if (!$this->godChildren->contains($godChild)) {
            $this->godChildren->add($godChild);
            $godChild->setGodChild($this);
        }

        return $this;
    }

    public function removeGodChild(Sponsor $godChild): static
    {
        if ($this->godChildren->removeElement($godChild)) {
            // set the owning side to null (unless already changed)
            if ($godChild->getGodChild() === $this) {
                $godChild->setGodChild(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, Characteristic>
     */
    public function getCharacteristics(): Collection
    {
        return $this->characteristics;
    }

    public function addCharacteristic(Characteristic $characteristic): static
    {
        if (!$this->characteristics->contains($characteristic)) {
            $this->characteristics->add($characteristic);
            $characteristic->setPerson($this);
        }

        return $this;
    }

    public function removeCharacteristic(Characteristic $characteristic): static
    {
        if ($this->characteristics->removeElement($characteristic)) {
            // set the owning side to null (unless already changed)
            if ($characteristic->getPerson() === $this) {
                $characteristic->setPerson(null);
            }
        }

        return $this;
    }
}
