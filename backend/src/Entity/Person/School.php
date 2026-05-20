<?php

declare(strict_types=1);

namespace App\Entity\Person;

use App\Repository\Person\SchoolRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SchoolRepository::class)]
final class School
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    /**
     * @var Collection<int, PersonFiliere>
     */
    #[ORM\OneToMany(targetEntity: PersonFiliere::class, mappedBy: 'school', orphanRemoval: false)]
    private Collection $personFilieres;

    public function __construct()
    {
        $this->personFilieres = new ArrayCollection();
    }

    public static function normalize(string $name): string
    {
        return trim($name);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * @return Collection<int, PersonFiliere>
     */
    public function getPersonFilieres(): Collection
    {
        return $this->personFilieres;
    }
}
