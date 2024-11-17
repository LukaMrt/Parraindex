<?php

declare(strict_types=1);

namespace App\Entity\Person;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private const string DEFAULT_PICTURE = 'no-picture.svg';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var string[] $roles
     */
    #[ORM\Column(type: "json")]
    private array $roles;

    /**
     * @var ?string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $person = null;

    #[ORM\Column(nullable: true)]
    private ?string $picture = null;

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
        return (string) $this->email;
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
        return array_map(static fn (string $role) => Role::from($role), $this->getRoles());
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
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(Person $person): static
    {
        $this->person = $person;

        return $this;
    }

    public function isAdmin(): bool
    {
        return in_array(Role::ADMIN, $this->getRolesEnum());
    }

    public function getPicture(): string
    {
        return $this->picture ?? self::DEFAULT_PICTURE;
    }

    public function setPicture(string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }
}
