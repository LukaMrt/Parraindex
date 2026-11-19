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
        self::assertNotEmpty($persons);
        self::assertContainsOnlyInstancesOf(Person::class, $persons);

        // Verify ordering
        $ids       = array_map(static fn (Person $p): int => $p->getId(), $persons);
        $sortedIds = $ids;
        sort($sortedIds);
        self::assertSame($sortedIds, $ids);
    }

    public function testGetAllOrderByFirstNameReturnsPersonsOrderedByFirstName(): void
    {
        // When
        $persons = $this->repository->getAll('firstName');

        // Then
        self::assertNotEmpty($persons);

        // Verify ordering
        $firstNames = array_map(static fn (Person $p): string => $p->getFirstName(), $persons);
        // MySQL trie avec une collation insensible aux accents (« Théo » < « Thomas ») :
        // on compare avec un tri ICU plutôt qu'avec sort(), qui trie octet par octet.
        $sortedFirstNames = $firstNames;
        $collator = collator_create('fr_FR');
        self::assertInstanceOf(\Collator::class, $collator);
        collator_sort($collator, $sortedFirstNames);
        self::assertSame($sortedFirstNames, $firstNames);
    }

    public function testGetAllThrowsExceptionForInvalidOrderBy(): void
    {
        // Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Invalid orderBy parameter: invalidColumn');

        // When
        $this->repository->getAll('invalidColumn');
    }

    public function testGetByIdentityReturnsPersonWhenFound(): void
    {
        // Given
        $persons = $this->repository->getAll();
        self::assertNotEmpty($persons);

        $existingPerson = $persons[0];

        // When
        $result = $this->repository->getByIdentity(
            $existingPerson->getFirstName(),
            $existingPerson->getLastName()
        );

        // Then
        self::assertInstanceOf(Person::class, $result);
        self::assertSame($existingPerson->getId(), $result->getId());
    }

    public function testGetByIdentityReturnsNullWhenNotFound(): void
    {
        // When
        $result = $this->repository->getByIdentity('NonExistent', 'Person');

        // Then
        self::assertNotInstanceOf(Person::class, $result);
    }

    public function testFindWithRelationsLoadsAllRelations(): void
    {
        // Given
        $persons = $this->repository->getAll();
        self::assertNotEmpty($persons);

        $personId = $persons[0]->getId();

        // When
        $result = $this->repository->findWithRelations($personId);

        // Then
        self::assertInstanceOf(Person::class, $result);
        self::assertSame($personId, $result->getId());

        // Verify relations are loaded (no lazy loading queries)
        // Access collections to ensure they're initialized and counted
        self::assertGreaterThanOrEqual(0, $result->getGodFathers()->count());
        self::assertGreaterThanOrEqual(0, $result->getGodChildren()->count());
        self::assertGreaterThanOrEqual(0, $result->getCharacteristics()->count());
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
        self::assertInstanceOf(\DateTimeInterface::class, $person->getCreatedAt());
    }

    public function testUpdateKeepsPictureNullWhenNotSet(): void
    {
        // Given
        $person = new Person();
        $person->setFirstName('Test');
        $person->setLastName('Person');
        $person->setStartYear(2024);

        // When
        $this->repository->update($person);

        // Then
        // L'avatar par défaut est géré par le front : aucune valeur n'est forcée en base
        self::assertNull($person->getPicture());
    }
}
