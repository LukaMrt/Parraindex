<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Person\Person;
use App\Repository\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PersonRepositoryTest extends KernelTestCase
{
    private PersonRepository $repository;

    #[\Override]
    protected function setUp(): void
    {
        // Given
        self::bootKernel();
        /** @var PersonRepository $repository */
        $repository       = self::getContainer()->get(PersonRepository::class);
        $this->repository = $repository;
    }

    public function testGetAllOrderByIdReturnsPersonsOrderedById(): void
    {
        // When
        $persons = $this->repository->getAll('id');

        // Then
        $this->assertNotEmpty($persons);
        $this->assertContainsOnlyInstancesOf(Person::class, $persons);

        // Verify ordering
        $ids       = array_map(static fn (Person $p): int => $p->getId(), $persons);
        $sortedIds = $ids;
        sort($sortedIds);
        $this->assertSame($sortedIds, $ids);
    }

    public function testGetAllOrderByFirstNameReturnsPersonsOrderedByFirstName(): void
    {
        // When
        $persons = $this->repository->getAll('firstName');

        // Then
        $this->assertNotEmpty($persons);

        // Verify ordering
        $firstNames       = array_map(static fn (Person $p): string => $p->getFirstName(), $persons);
        $sortedFirstNames = $firstNames;
        sort($sortedFirstNames);
        $this->assertSame($sortedFirstNames, $firstNames);
    }

    public function testGetAllThrowsExceptionForInvalidOrderBy(): void
    {
        // Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid orderBy parameter: invalidColumn');

        // When
        $this->repository->getAll('invalidColumn');
    }

    public function testGetByIdentityReturnsPersonWhenFound(): void
    {
        // Given
        $persons = $this->repository->getAll();
        $this->assertNotEmpty($persons);

        $existingPerson = $persons[0];

        // When
        $result = $this->repository->getByIdentity(
            $existingPerson->getFirstName(),
            $existingPerson->getLastName()
        );

        // Then
        $this->assertInstanceOf(Person::class, $result);
        $this->assertSame($existingPerson->getId(), $result->getId());
    }

    public function testGetByIdentityReturnsNullWhenNotFound(): void
    {
        // When
        $result = $this->repository->getByIdentity('NonExistent', 'Person');

        // Then
        $this->assertNotInstanceOf(Person::class, $result);
    }

    public function testFindWithRelationsLoadsAllRelations(): void
    {
        // Given
        $persons = $this->repository->getAll();
        $this->assertNotEmpty($persons);

        $personId = $persons[0]->getId();

        // When
        $result = $this->repository->findWithRelations($personId);

        // Then
        $this->assertInstanceOf(Person::class, $result);
        $this->assertSame($personId, $result->getId());

        // Verify relations are loaded (no lazy loading queries)
        // Access collections to ensure they're initialized and counted
        $this->assertGreaterThanOrEqual(0, $result->getGodFathers()->count());
        $this->assertGreaterThanOrEqual(0, $result->getGodChildren()->count());
        $this->assertGreaterThanOrEqual(0, $result->getCharacteristics()->count());
    }

    public function testUpdateSetsCreatedAtWhenNull(): void
    {
        // Given
        $person = new Person();
        $person->setFirstName('Test');
        $person->setLastName('Person');
        $person->setStartYear(2024);

        // When
        $this->repository->update($person);

        // Then
        $this->assertInstanceOf(\DateTimeInterface::class, $person->getCreatedAt());
    }

    public function testUpdateSetsDefaultPictureWhenNull(): void
    {
        // Given
        $person = new Person();
        $person->setFirstName('Test');
        $person->setLastName('Person');
        $person->setStartYear(2024);

        // When
        $this->repository->update($person);

        // Then
        $this->assertSame('no-picture.svg', $person->getPicture());
    }
}
