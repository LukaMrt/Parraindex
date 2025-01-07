<?php

declare(strict_types=1);

namespace App\Entity\Person;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: 'email', message: 'Un compte existe déjà avec cette adresse email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private const string DEFAULT_PICTURE = 'no-picture.svg';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\Email]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z-]+\.[a-zA-Z-]+@etu\.univ-lyon1\.fr$/',
        message: 'L\'adresse mail doit être une adresse universitaire de Lyon 1'
    )]
    private ?string $email = null;

    /**
     * @var string[] $roles
     */
    #[ORM\Column(type: Types::JSON)]
    private array $roles = [Role::USER->value];

    /**
     * @var ?string The hashed password
     */
    #[ORM\Column]
    #[Assert\Length(
        min: 6,
        max: 4096,
        minMessage: 'Votre mot de passe doit faire au moins {{ limit }} caractères'
    )]
    #[Assert\NotBlank]
    #[Assert\NotCompromisedPassword(message: 'Ce mot de passe a déjà été compromis. Veuillez en choisir un autre')]
    #[Assert\PasswordStrength(
        minScore: Assert\PasswordStrength::STRENGTH_WEAK,
        message: 'Votre mot de passe est trop simple'
    )]
    private ?string $password = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $person = null;

    #[ORM\Column(nullable: true)]
    private ?string $picture = null;

    #[ORM\Column]
    private bool $isVerified = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    #[\Override]
    public function getUserIdentifier(): string
    {
        if ($this->email === null || $this->email === '' || $this->email === '0') {
            throw new \LogicException('The email of the user is not set.');
        }

        return $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return string[]
     */
    #[\Override]
    public function getRoles(): array
    {
        if (!in_array(Role::USER->value, $this->roles)) {
            $this->roles[] = Role::USER->value;
        }

        return $this->roles;
    }

    /**
     * @return Role[]
     */
    public function getRolesEnum(): array
    {
        return array_map(static fn (string $role): Role => Role::from($role), $this->getRoles());
    }

    /**
     * @param Role[] $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = array_map(static fn (Role $role) => $role->value, $roles);

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    #[\Override]
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    #[\Override]
    public function eraseCredentials(): void
    {
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(Person $person): static
    {
        $this->person = $person;

        return $this;
    }

    // Used in twig templates
    public function isAdmin(): bool
    {
        return in_array(Role::ADMIN, $this->getRolesEnum());
    }

    public function setPicture(string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    // Used in twig templates
    public function getPicture(): string
    {
        return $this->picture ?? self::DEFAULT_PICTURE;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
}
