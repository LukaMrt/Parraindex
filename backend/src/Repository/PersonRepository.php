<?php

declare(strict_types=1);

namespace App\Repository;

use SortDirection;
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
     * @return list<Person>
     */
    public function getAll(string $orderBy = 'id', ?string $q = null, int $limit = 20): array
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

        $qb = $this->createQueryBuilder('p')
            ->select('p.id')
            ->orderBy('p.' . $orderBy, SortDirection::Ascending)
            ->addOrderBy('p.id', SortDirection::Ascending);

        if ($q !== null && trim($q) !== '') {
            $escaped = addcslashes(trim($q), '%_\\');
            $qb->andWhere('LOWER(p.firstName) LIKE LOWER(:q) OR LOWER(p.lastName) LIKE LOWER(:q)')
                ->setParameter('q', '%' . $escaped . '%')
                ->setMaxResults(max(1, min($limit, 100)));
        }

        /** @var int[] $ids */
        $ids = $qb->getQuery()->getSingleColumnResult();

        // Hydrate les relations puis restaure l'ordre demandé
        $byId = [];
        foreach ($this->findAllWithRelationsByIds($ids) as $person) {
            $byId[$person->getId()] = $person;
        }

        return array_values(array_filter(
            array_map(static fn(int $id): ?Person => $byId[$id] ?? null, $ids),
            static fn(?Person $p): bool => $p instanceof Person,
        ));
    }

    public function getByIdentity(string $firstName, string $lastName): ?Person
    {
        return $this->findOneBy(
            [
                'firstName' => $firstName,
                'lastName' => $lastName
            ]
        );
    }

    public function getById(int $id): ?Person
    {
        return $this->find($id);
    }

    public function findWithRelations(int $id): ?Person
    {
        return $this->findAllWithRelationsByIds([$id])[0] ?? null;
    }

    /**
     * @return Person[]
     */
    public function findPaginated(int $offset, int $limit): array
    {
        /** @var int[] $ids */
        $ids = $this->createQueryBuilder('p')
            ->select('p.id')
            ->orderBy('p.id', SortDirection::Ascending)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getSingleColumnResult();

        return $this->findAllWithRelationsByIds($ids);
    }

    /**
     * Charge les personnes et toutes leurs relations en hydratation multi-étapes :
     * une requête par groupe de collections plutôt qu'un seul gros JOIN, qui produirait
     * un produit cartésien (parrains × fillots × caractéristiques × filières × …).
     * Les requêtes suivantes réutilisent les entités déjà gérées (identity map) et
     * initialisent leurs collections au passage.
     *
     * @param int[] $ids
     * @return Person[] triées par id
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
            ->orderBy('p.id', SortDirection::Ascending)
            ->getQuery()
            ->getResult();

        if ($result === []) {
            return [];
        }

        $this->createQueryBuilder('p')
            ->leftJoin('p.characteristics', 'c')->addSelect('c')
            ->leftJoin('c.characteristicType', 'ct')->addSelect('ct')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        $this->createQueryBuilder('p')
            ->leftJoin('p.filieres', 'pf')->addSelect('pf')
            ->leftJoin('pf.filiere', 'f')->addSelect('f')
            ->leftJoin('pf.school', 's')->addSelect('s')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        $this->createQueryBuilder('p')
            ->leftJoin('p.associations', 'pa')->addSelect('pa')
            ->leftJoin('pa.association', 'assoc')->addSelect('assoc')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        $this->createQueryBuilder('p')
            ->leftJoin('p.links', 'l')->addSelect('l')
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
            ->orderBy('p.startYear', SortDirection::Ascending)
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
                Join::ON,
                'u.person = p.id'
            )
            ->where('u.id IS NULL')
            ->orderBy('p.lastName', SortDirection::Ascending)
            ->addOrderBy('p.firstName', SortDirection::Ascending)
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
                Join::ON,
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
                Join::ON,
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
