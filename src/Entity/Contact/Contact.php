<?php

declare(strict_types=1);

namespace App\Entity\Contact;

use App\Entity\Sponsor\Type as SponsorType;
use App\Repository\ContactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $contacterFirstName = null;

    #[ORM\Column(length: 255)]
    private ?string $contacterLastName = null;

    #[ORM\Column(length: 255)]
    #[Assert\Email]
    private ?string $contacterEmail = null;

    #[ORM\Column]
    private ?Type $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $resolutionDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\When(
        expression: 'this.getType().value in [0, 1, 2, 3, 4, 5, 7]',
        constraints: [new Assert\NotNull()]
    )]
    private ?string $relatedPersonFirstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\When(
        expression: 'this.getType().value in [0, 1, 2, 3, 4, 5, 7]',
        constraints: [new Assert\NotNull()]
    )]
    private ?string $relatedPersonLastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\When(
        expression: 'this.getType().value in [3, 4, 5]',
        constraints: [new Assert\NotNull()]
    )]
    private ?string $relatedPerson2FirstName = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\When(
        expression: 'this.getType().value in [0]',
        constraints: [new Assert\NotNull()]
    )]
    private ?int $entryYear = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\When(
        expression: 'this.getType().value in [3, 4, 5]',
        constraints: [new Assert\NotNull()]
    )]
    private ?string $relatedPerson2LastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\When(
        expression: 'this.getType().value in [3]',
        constraints: [new Assert\NotNull()]
    )]
    private ?SponsorType $sponsorType = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\When(
        expression: 'this.getType().value in [3]',
        constraints: [new Assert\NotNull()]
    )]
    private ?\DateTime $sponsorDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\When(
        expression: 'this.getType().value in [9]',
        constraints: [
            new Assert\NotNull(),
            new Assert\Length(
                min: 6,
                max: 4096,
                minMessage: 'Votre mot de passe doit faire au moins {{ limit }} caractères'
            ),
            new Assert\NotCompromisedPassword(
                message: 'Ce mot de passe a déjà été compromis. Veuillez en choisir un autre'
            ),
            new Assert\PasswordStrength(
                minScore: Assert\PasswordStrength::STRENGTH_WEAK,
                message: 'Votre mot de passe est trop simple'
            )
        ]
    )]
    private ?string $password = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getContacterFirstName(): string
    {
        /** @var string $contacterFirstName */
        $contacterFirstName = $this->contacterFirstName;
        return $contacterFirstName;
    }

    public function setContacterFirstName(string $contacterFirstName): static
    {
        $this->contacterFirstName = $contacterFirstName;

        return $this;
    }

    public function getContacterLastName(): string
    {
        /** @var string $contacterLastName */
        $contacterLastName = $this->contacterLastName;
        return $contacterLastName;
    }

    public function setContacterLastName(string $contacterLastName): static
    {
        $this->contacterLastName = $contacterLastName;

        return $this;
    }

    public function getContacterEmail(): string
    {
        /** @var string $contacterEmail */
        $contacterEmail = $this->contacterEmail;
        return $contacterEmail;
    }

    public function setContacterEmail(string $contacterEmail): static
    {
        $this->contacterEmail = $contacterEmail;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getResolutionDate(): ?\DateTimeInterface
    {
        return $this->resolutionDate;
    }

    public function setResolutionDate(?\DateTimeInterface $resolutionDate): static
    {
        $this->resolutionDate = $resolutionDate;

        return $this;
    }

    public function getRelatedPersonFirstName(): string
    {
        return $this->relatedPersonFirstName ?? '';
    }

    public function setRelatedPersonFirstName(?string $relatedPersonFirstName): static
    {
        $this->relatedPersonFirstName = $relatedPersonFirstName;

        return $this;
    }

    public function getRelatedPersonLastName(): string
    {
        return $this->relatedPersonLastName ?? '';
    }

    public function setRelatedPersonLastName(?string $relatedPersonLastName): static
    {
        $this->relatedPersonLastName = $relatedPersonLastName;

        return $this;
    }

    public function getEntryYear(): int
    {
        return $this->entryYear ?? 2022;
    }

    public function setEntryYear(int $entryYear): static
    {
        $this->entryYear = $entryYear;

        return $this;
    }

    public function getRelatedPerson2FirstName(): string
    {
        return $this->relatedPerson2FirstName ?? '';
    }

    public function setRelatedPerson2FirstName(?string $relatedPerson2FirstName): static
    {
        $this->relatedPerson2FirstName = $relatedPerson2FirstName;

        return $this;
    }

    public function getRelatedPerson2LastName(): string
    {
        return $this->relatedPerson2LastName ?? '';
    }

    public function setRelatedPerson2LastName(?string $relatedPerson2LastName): static
    {
        $this->relatedPerson2LastName = $relatedPerson2LastName;

        return $this;
    }

    public function getSponsorType(): SponsorType
    {
        return $this->sponsorType ?? SponsorType::UNKNOWN;
    }

    public function setSponsorType(SponsorType $sponsorType): static
    {
        $this->sponsorType = $sponsorType;

        return $this;
    }

    public function getSponsorDate(): ?\DateTime
    {
        return $this->sponsorDate;
    }

    public function setSponsorDate(?\DateTime $sponsorDate): static
    {
        $this->sponsorDate = $sponsorDate;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password ?? '';
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }
}
