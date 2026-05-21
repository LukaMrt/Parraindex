<?php

declare(strict_types=1);

namespace App\Repository\Person;

use App\Entity\Person\Association;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Association>
 */
class AssociationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Association::class);
    }

    /**
     * @return Association[]
     */
    public function findAllOrderedByName(): array
    {
        /** @var Association[] $result */
        $result = $this->createQueryBuilder('a')
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function findByName(string $name): ?Association
    {
        /** @var Association|null $result */
        $result = $this->createQueryBuilder('a')
            ->andWhere('LOWER(a.name) = LOWER(:name)')
            ->setParameter('name', trim($name))
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }
}
