<?php

declare(strict_types=1);

namespace App\Repository\Person;

use App\Entity\Person\School;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<School>
 */
class SchoolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, School::class);
    }

    /**
     * @return School[]
     */
    public function findAllOrderedByName(): array
    {
        /** @var School[] $result */
        $result = $this->createQueryBuilder('s')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function findByName(string $name): ?School
    {
        /** @var School|null $result */
        $result = $this->createQueryBuilder('s')
            ->andWhere('LOWER(s.name) = LOWER(:name)')
            ->setParameter('name', trim($name))
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }
}
