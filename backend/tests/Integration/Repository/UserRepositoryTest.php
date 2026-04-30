<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use App\Entity\Person\Person;
use App\Entity\Person\Role;
use App\Entity\Person\User;
use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserRepositoryTest extends KernelTestCase
{
    private UserRepository $repository;

    private PersonRepository $personRepository;

    #[\Override]
    protected function setUp(): void
    {
        // Given
        self::bootKernel();
        /** @var UserRepository $repository */
        $repository       = self::getContainer()->get(UserRepository::class);
        $this->repository = $repository;

        /** @var PersonRepository $personRepository */
        $personRepository       = self::getContainer()->get(PersonRepository::class);
        $this->personRepository = $personRepository;
    }

    public function testUpdateSetsCreatedAtWhenNull(): void
    {
        // Given
        $person = $this->createPerson('Test', 'User', 2024);

        $user = new User();
        $user->setEmail('test.user@etu.univ-lyon1.fr');
        $user->setPassword('hashed_password');
        $user->setRoles([Role::USER]);
        $user->setPerson($person);

        // When
        $this->repository->update($user);

        // Then
        $this->assertInstanceOf(\DateTimeInterface::class, $user->getCreatedAt());
    }

    public function testUpdateDoesNotOverrideExistingCreatedAt(): void
    {
        // Given
        $person = $this->createPerson('Test', 'User2', 2024);

        $existingDate = new \DateTimeImmutable('2023-01-01 10:00:00');
        $user = new User();
        $user->setEmail('test.user2@etu.univ-lyon1.fr');
        $user->setPassword('hashed_password');
        $user->setRoles([Role::USER]);
        $user->setPerson($person);
        $user->setCreatedAt($existingDate);

        // When
        $this->repository->update($user);

        // Then
        $this->assertEquals($existingDate, $user->getCreatedAt());
    }

    public function testUpgradePasswordUpdatesPasswordHash(): void
    {
        // Given
        $person = $this->createPerson('Test', 'Upgrade', 2024);

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail('test.upgrade@etu.univ-lyon1.fr');
        $user->setPassword('old_hashed_password');
        $user->setRoles([Role::USER]);
        $user->setPerson($person);

        $this->repository->update($user);

        $newPassword = $hasher->hashPassword($user, 'new_password');

        // When
        $this->repository->upgradePassword($user, $newPassword);

        // Then
        $this->assertSame($newPassword, $user->getPassword());

        // Verify in database
        $updatedUser = $this->repository->findOneBy(['email' => 'test.upgrade@etu.univ-lyon1.fr']);
        $this->assertInstanceOf(User::class, $updatedUser);
        $this->assertSame($newPassword, $updatedUser->getPassword());
    }

    public function testUpgradePasswordThrowsExceptionForInvalidUser(): void
    {
        // Given
        $invalidUser = new class implements PasswordAuthenticatedUserInterface {
            public function getPassword(): string
            {
                return 'password';
            }
        };

        // Then
        $this->expectException(UnsupportedUserException::class);

        // When
        $this->repository->upgradePassword($invalidUser, 'new_password');
    }

    private function createPerson(string $firstName, string $lastName, int $startYear): Person
    {
        $person = new Person();
        $person->setFirstName($firstName);
        $person->setLastName($lastName);
        $person->setStartYear($startYear);

        $this->personRepository->update($person);

        return $person;
    }
}
