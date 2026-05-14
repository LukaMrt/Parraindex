<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Person\Person;
use App\Entity\Sponsor\Sponsor;
use App\Entity\Sponsor\Type;
use App\Repository\PersonRepository;
use App\Repository\SponsorRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SponsorRepositoryTest extends KernelTestCase
{
    private SponsorRepository $repository;

    private PersonRepository $personRepository;

    #[\Override]
    protected function setUp(): void
    {
        // Given
        self::bootKernel();
        /** @var SponsorRepository $repository */
        $repository       = self::getContainer()->get(SponsorRepository::class);
        $this->repository = $repository;

        /** @var PersonRepository $personRepository */
        $personRepository       = self::getContainer()->get(PersonRepository::class);
        $this->personRepository = $personRepository;
    }

    public function testGetByIdReturnsExistingSponsor(): void
    {
        // Given - Create a sponsor first
        $godFather = $this->createPerson('GodFather', 'Test', 2020);
        $godChild  = $this->createPerson('GodChild', 'Test', 2021);

        $sponsor = new Sponsor();
        $sponsor->setGodFather($godFather);
        $sponsor->setGodChild($godChild);
        $sponsor->setType(Type::CLASSIC);

        $this->repository->create($sponsor);
        $sponsorId = $sponsor->getId();

        // When
        $this->assertNotNull($sponsorId);
        $result = $this->repository->getById($sponsorId);

        // Then
        $this->assertInstanceOf(Sponsor::class, $result);
        $this->assertSame($sponsorId, $result->getId());
    }

    public function testGetByIdReturnsNullForNonExistentId(): void
    {
        // When
        $result = $this->repository->getById(999999);

        // Then
        $this->assertNotInstanceOf(Sponsor::class, $result);
    }

    public function testGetByPeopleIdsReturnsExistingSponsor(): void
    {
        // Given
        $godFather = $this->createPerson('Father', 'Find', 2020);
        $godChild  = $this->createPerson('Child', 'Find', 2021);

        $sponsor = new Sponsor();
        $sponsor->setGodFather($godFather);
        $sponsor->setGodChild($godChild);
        $sponsor->setType(Type::HEART);

        $this->repository->create($sponsor);

        // When
        $result = $this->repository->getByPeopleIds($godFather->getId(), $godChild->getId());

        // Then
        $this->assertInstanceOf(Sponsor::class, $result);
        $this->assertInstanceOf(Person::class, $result->getGodFather());
        $this->assertInstanceOf(Person::class, $result->getGodChild());
        $this->assertSame($godFather->getId(), $result->getGodFather()->getId());
        $this->assertSame($godChild->getId(), $result->getGodChild()->getId());
    }

    public function testGetByPeopleIdsReturnsNullWhenNotFound(): void
    {
        // When
        $result = $this->repository->getByPeopleIds(999999, 999998);

        // Then
        $this->assertNotInstanceOf(Sponsor::class, $result);
    }

    public function testCreateSetsCreatedAtWhenNull(): void
    {
        // Given
        $godFather = $this->createPerson('Create', 'Father', 2020);
        $godChild  = $this->createPerson('Create', 'Child', 2021);

        $sponsor = new Sponsor();
        $sponsor->setGodFather($godFather);
        $sponsor->setGodChild($godChild);

        // When
        $this->repository->create($sponsor);

        // Then
        $this->assertEqualsWithDelta(time(), $sponsor->getCreatedAt()->getTimestamp(), 5);
    }

    public function testCreateSetsDefaultTypeWhenNull(): void
    {
        // Given
        $godFather = $this->createPerson('Type', 'Father', 2020);
        $godChild  = $this->createPerson('Type', 'Child', 2021);

        $sponsor = new Sponsor();
        $sponsor->setGodFather($godFather);
        $sponsor->setGodChild($godChild);

        // When
        $this->repository->create($sponsor);

        // Then
        $this->assertSame(Type::UNKNOWN, $sponsor->getType());
    }

    public function testUpdateModifiesExistingSponsor(): void
    {
        // Given
        $godFather = $this->createPerson('Update', 'Father', 2020);
        $godChild  = $this->createPerson('Update', 'Child', 2021);

        $sponsor = new Sponsor();
        $sponsor->setGodFather($godFather);
        $sponsor->setGodChild($godChild);
        $sponsor->setType(Type::CLASSIC);

        $this->repository->create($sponsor);

        // When
        $sponsor->setType(Type::HEART);
        $this->repository->update($sponsor);

        // Then
        $sponsorId = $sponsor->getId();
        $this->assertNotNull($sponsorId);
        $updatedSponsor = $this->repository->getById($sponsorId);
        $this->assertInstanceOf(Sponsor::class, $updatedSponsor);
        $this->assertSame(Type::HEART, $updatedSponsor->getType());
    }

    public function testDeleteRemovesSponsor(): void
    {
        // Given
        $godFather = $this->createPerson('Delete', 'Father', 2020);
        $godChild  = $this->createPerson('Delete', 'Child', 2021);

        $sponsor = new Sponsor();
        $sponsor->setGodFather($godFather);
        $sponsor->setGodChild($godChild);

        $this->repository->create($sponsor);
        $sponsorId = $sponsor->getId();

        // When
        $this->assertNotNull($sponsorId);
        $this->repository->delete($sponsor);

        // Then
        $result = $this->repository->getById($sponsorId);
        $this->assertNotInstanceOf(Sponsor::class, $result);
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
