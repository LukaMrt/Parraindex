<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Person\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Person>
 */
class PersonRepository extends ServiceEntityRepository
{
    public const string DEFAULT_PICTURE = 'no-picture.svg';

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Person::class);
    }

    /**
     * @return Person[]
     */
    public function getAll(string $orderBy = 'id'): array
    {
        /** @var Person[] $result */
        $result = $this->createQueryBuilder('p')
            ->orderBy('p.' . $orderBy, 'ASC')
            ->getQuery()
            ->getResult();
        return $result;
    }

    public function getByIdentity(string $firstName, string $lastName): ?Person
    {
        return $this->findOneBy(
            [
                'firstName' => $firstName,
                'lastName' => $lastName
            ]
        ) ?? null;
    }

    public function getById(int $id): ?Person
    {
        return $this->find($id);
    }

    public function getByEmail(string $email): ?Person
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function update(Person $person): void
    {
        if (!$person->getCreatedAt() instanceof \DateTimeInterface) {
            $person->setCreatedAt(new \DateTimeImmutable());
        }

        if ($person->getPicture() === null) {
            $person->setPicture(self::DEFAULT_PICTURE);
        }

        $this->getEntityManager()->persist($person);
        $this->getEntityManager()->flush();
    }

    public function delete(Person $person): void
    {
        $this->getEntityManager()->remove($person);
        $this->getEntityManager()->flush();
    }
}
