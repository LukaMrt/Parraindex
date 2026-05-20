<?php

declare(strict_types=1);

namespace App\Entity\Person;

use App\Entity\Person\PersonFiliere;
use App\Repository\Person\FiliereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FiliereRepository::class)]
final class Filiere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, PersonFiliere>
     */
    #[ORM\OneToMany(targetEntity: PersonFiliere::class, mappedBy: 'filiere', orphanRemoval: true)]
    private Collection $persons;

    public function __construct()
    {
        $this->persons = new ArrayCollection();
    }

    public static function normalize(string $name): string
    {
        return ucfirst(strtolower(trim($name)));
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

    /**
     * @return Collection<int, PersonFiliere>
     */
    public function getPersons(): Collection
    {
        return $this->persons;
    }

    public function addPerson(PersonFiliere $person): static
    {
        if (!$this->persons->contains($person)) {
            $this->persons->add($person);
            $person->setFiliere($this);
        }

        return $this;
    }

    public function removePerson(PersonFiliere $person): static
    {
        // set the owning side to null (unless already changed)
        if ($this->persons->removeElement($person) && $person->getFiliere() === $this) {
            $person->setFiliere(null);
        }

        return $this;
    }
}
