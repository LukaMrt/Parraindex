<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Person\User;
use Doctrine\ORM\Query\Expr\Join;
use App\Entity\Person\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Person>
 */
class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Person::class);
    }

    /**
     * @return Person[]
     */
    public function getAll(string $orderBy = 'id'): array
    {
        $allowedColumns = [
            'id',
            'firstName',
            'lastName',
            'startYear',
            'createdAt',
        ];

        if (!in_array($orderBy, $allowedColumns, true)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid orderBy parameter: %s. Allowed values: %s', $orderBy, implode(', ', $allowedColumns))
            );
        }

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

    public function findWithRelations(int $id): ?Person
    {
        /** @var Person|null $result */
        $result = $this->createQueryBuilder('p')
            ->leftJoin('p.godFathers', 'gf')->addSelect('gf')
            ->leftJoin('gf.godFather', 'gfp')->addSelect('gfp')
            ->leftJoin('p.godChildren', 'gc')->addSelect('gc')
            ->leftJoin('gc.godChild', 'gcp')->addSelect('gcp')
            ->leftJoin('p.characteristics', 'c')->addSelect('c')
            ->leftJoin('c.characteristicType', 'ct')->addSelect('ct')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    /**
     * @return Person[]
     */
    public function findAllWithSponsors(): array
    {
        /** @var Person[] $result */
        $result = $this->createQueryBuilder('p')
            ->leftJoin('p.godFathers', 'gf')->addSelect('gf')
            ->leftJoin('gf.godFather', 'gfp')->addSelect('gfp')
            ->leftJoin('p.godChildren', 'gc')->addSelect('gc')
            ->leftJoin('gc.godChild', 'gcp')->addSelect('gcp')
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return Person[]
     */
    public function findPaginated(int $offset, int $limit): array
    {
        $ids = $this->createQueryBuilder('p')
            ->select('p.id')
            ->orderBy('p.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getSingleColumnResult();

        if ($ids === []) {
            return [];
        }

        /** @var Person[] $result */
        $result = $this->createQueryBuilder('p')
            ->leftJoin('p.godFathers', 'gf')->addSelect('gf')
            ->leftJoin('gf.godFather', 'gfp')->addSelect('gfp')
            ->leftJoin('p.godChildren', 'gc')->addSelect('gc')
            ->leftJoin('gc.godChild', 'gcp')->addSelect('gcp')
            ->leftJoin('p.characteristics', 'c')->addSelect('c')
            ->leftJoin('c.characteristicType', 'ct')->addSelect('ct')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @param int[] $ids
     * @return Person[]
     */
    public function findAllWithRelationsByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        /** @var Person[] $result */
        $result = $this->createQueryBuilder('p')
            ->leftJoin('p.godFathers', 'gf')->addSelect('gf')
            ->leftJoin('gf.godFather', 'gfp')->addSelect('gfp')
            ->leftJoin('p.godChildren', 'gc')->addSelect('gc')
            ->leftJoin('gc.godChild', 'gcp')->addSelect('gcp')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<array{startYear: int, count: int}>
     */
    public function countByStartYear(): array
    {
        /** @var array<array{startYear: int, count: int}> $result */
        $result = $this->createQueryBuilder('p')
            ->select('p.startYear AS startYear, COUNT(p.id) AS count')
            ->groupBy('p.startYear')
            ->orderBy('p.startYear', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_map(
            static fn (array $row): array => [
                'startYear' => $row['startYear'],
                'count'     => $row['count'],
            ],
            $result,
        );
    }

    public function getByEmail(string $email): ?Person
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * @return Person[]
     */
    public function findWithoutUser(): array
    {
        /** @var Person[] $result */
        $result = $this->createQueryBuilder('p')
            ->leftJoin(
                User::class,
                'u',
                Join::WITH,
                'u.person = p.id'
            )
            ->where('u.id IS NULL')
            ->orderBy('p.lastName', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return Person[]
     */
    public function findWithoutUserPaginated(int $offset, int $limit): array
    {
        /** @var Person[] $result */
        $result = $this->createQueryBuilder('p')
            ->leftJoin(
                User::class,
                'u',
                Join::WITH,
                'u.person = p.id'
            )
            ->where('u.id IS NULL')
            ->orderBy('p.lastName', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function countWithoutUser(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->leftJoin(
                User::class,
                'u',
                Join::WITH,
                'u.person = p.id'
            )
            ->where('u.id IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function hasLinkedUser(int $personId): bool
    {
        $count = (int) $this->createQueryBuilder('p')
            ->select('COUNT(u.id)')
            ->join(
                User::class,
                'u',
                Join::WITH,
                'u.person = p.id'
            )
            ->where('p.id = :personId')
            ->setParameter('personId', $personId)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function create(Person $person): void
    {
        $this->update($person);
    }

    public function update(Person $person): void
    {
        if (!$person->getCreatedAt() instanceof \DateTimeInterface) {
            $person->setCreatedAt(new \DateTimeImmutable());
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
