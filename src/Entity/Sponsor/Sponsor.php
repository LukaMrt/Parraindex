<?php

declare(strict_types=1);

namespace App\Entity\Sponsor;

use App\Entity\Person\Person;
use App\Repository\SponsorRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SponsorRepository::class)]
class Sponsor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'godChildren')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $godFather = null;

    #[ORM\ManyToOne(inversedBy: 'godFathers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $godChild = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?Type $type = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGodFather(): ?Person
    {
        return $this->godFather;
    }

    public function setGodFather(?Person $person): static
    {
        $this->godFather = $person;

        return $this;
    }

    public function getGodChild(): ?Person
    {
        return $this->godChild;
    }

    public function setGodChild(?Person $person): static
    {
        $this->godChild = $person;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

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

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(Type $type): static
    {
        $this->type = $type;

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

    public function formatDate(string $format): string
    {
        if ($this->date instanceof \DateTimeInterface) {
            return $this->date->format($format);
        }

        return '';
    }
}
