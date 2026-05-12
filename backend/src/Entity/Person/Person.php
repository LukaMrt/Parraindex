<?php

declare(strict_types=1);

namespace App\Entity\Person;

use App\Entity\Characteristic\Characteristic;
use App\Entity\Characteristic\CharacteristicType;
use App\Entity\Sponsor\Sponsor;
use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[ORM\Index(name: 'idx_person_start_year', fields: ['startYear'])]
#[ORM\Index(name: 'idx_person_name', fields: ['firstName', 'lastName'])]
#[UniqueEntity(fields: ['firstName', 'lastName'], message: 'person.unique')]
#[Vich\Uploadable]
class Person implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[Vich\UploadableField(mapping: 'person_avatar', fileNameProperty: 'picture')]
    #[Assert\Image(
        maxSize: '5M',
        mimeTypes: [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
        ],
        maxWidth: 4096,
        maxHeight: 4096,
        detectCorrupted: true,
    )]
    private ?File $pictureFile = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\Column(type: Types::TEXT, length: 65_535, nullable: true)]
    private ?string $biography = null;

    #[ORM\Column(length: 255)]
    #[Assert\CssColor([Assert\CssColor::HEX_LONG])]
    private string $color;

    #[ORM\Column(type: Types::TEXT, length: 65_535, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\Positive]
    #[Assert\NotBlank]
    #[Assert\Range(min: 1900, max: 2100)]
    private ?int $startYear = null;

    /**
     * @var Collection<int, Sponsor>
     */
    #[ORM\OneToMany(targetEntity: Sponsor::class, mappedBy: 'godChild', orphanRemoval: true)]
    private Collection $godFathers;

    /**
     * @var Collection<int, Sponsor>
     */
    #[ORM\OneToMany(targetEntity: Sponsor::class, mappedBy: 'godFather', orphanRemoval: true)]
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
        $this->godFathers      = new ArrayCollection();
        $this->godChildren     = new ArrayCollection();
        $this->characteristics = new ArrayCollection();
        $this->createdAt       = new \DateTime();
        $this->color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }

    public function getId(): int
    {
        /** @var int $id */
        $id = $this->id;
        return $id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getFirstName(): string
    {
        /** @var string $firstName */
        $firstName = $this->firstName;
        return $firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = ucfirst(strtolower($firstName));

        return $this;
    }

    public function getLastName(): string
    {
        /** @var string $lastName */
        $lastName = $this->lastName;
        return $lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = ucfirst(strtolower($lastName));

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

    public function getPictureFile(): ?File
    {
        return $this->pictureFile;
    }

    public function setPictureFile(?File $pictureFile): static
    {
        $this->pictureFile = $pictureFile;

        if ($pictureFile instanceof File) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
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

    public function addGodFather(Sponsor $sponsor): static
    {
        if (!$this->godFathers->contains($sponsor)) {
            $this->godFathers->add($sponsor);
            $sponsor->setGodFather($this);
        }

        return $this;
    }

    public function removeGodFather(Sponsor $sponsor): static
    {
        // set the owning side to null (unless already changed)
        if ($this->godFathers->removeElement($sponsor) && $sponsor->getGodFather() === $this) {
            $sponsor->setGodFather(null);
        }

        return $this;
    }

    /**
     * @param Collection<int, Sponsor> $godFathers
     */
    public function setGodFathers(Collection $godFathers): static
    {
        $this->godFathers = $godFathers;

        return $this;
    }

    /**
     * @return Collection<int, Sponsor>
     */
    public function getGodChildren(): Collection
    {
        return $this->godChildren;
    }

    public function addGodChild(Sponsor $sponsor): static
    {
        if (!$this->godChildren->contains($sponsor)) {
            $this->godChildren->add($sponsor);
            $sponsor->setGodChild($this);
        }

        return $this;
    }

    public function removeGodChild(Sponsor $sponsor): static
    {
        // set the owning side to null (unless already changed)
        if ($this->godChildren->removeElement($sponsor) && $sponsor->getGodChild() === $this) {
            $sponsor->setGodChild(null);
        }

        return $this;
    }

    /**
     * @param Collection<int, Sponsor> $godChildren
     */
    public function setGodChildren(Collection $godChildren): static
    {
        $this->godChildren = $godChildren;

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
        // set the owning side to null (unless already changed)
        if ($this->characteristics->removeElement($characteristic) && $characteristic->getPerson() === $this) {
            $characteristic->setPerson(null);
        }

        return $this;
    }

    public function equals(?Person $person): bool
    {
        return $person instanceof Person && $this->getId() === $person->getId();
    }

    /** @param CharacteristicType[] $allTypes */
    public function createMissingCharacteristics(array $allTypes): void
    {
        foreach ($allTypes as $type) {
            $exists = $this->getCharacteristics()->exists(
                static fn (int $key, Characteristic $c): bool => $c->getType()?->equals($type) ?? false
            );

            if (!$exists) {
                $this->addCharacteristic(new Characteristic()->setVisible(false)->setType($type));
            }
        }
    }

    public function getFullName(): string
    {
        return ucfirst($this->getFirstName()) . ' ' . strtoupper($this->getLastName());
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }
}
