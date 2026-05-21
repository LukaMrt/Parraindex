<?php

declare(strict_types=1);

namespace App\Repository\Person;

use App\Entity\Person\Filiere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Filiere>
 */
class FiliereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Filiere::class);
    }

    /**
     * @return Filiere[]
     */
    public function findAllOrderedByName(): array
    {
        /** @var Filiere[] $result */
        $result = $this->createQueryBuilder('f')
            ->orderBy('f.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function findByName(string $name): ?Filiere
    {
        /** @var Filiere|null $result */
        $result = $this->createQueryBuilder('f')
            ->andWhere('f.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }
}
