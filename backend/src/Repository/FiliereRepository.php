<?php

declare(strict_types=1);

namespace App\Repository;

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

    public function existsByName(string $name): bool
    {
        return (bool) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->andWhere('f.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getSingleScalarResult();
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

    public function update(Filiere $filiere): void
    {
        $this->getEntityManager()->persist($filiere);
        $this->getEntityManager()->flush();
    }

    public function delete(Filiere $filiere): void
    {
        $this->getEntityManager()->remove($filiere);
        $this->getEntityManager()->flush();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function create(string $name): Filiere
    {
        $filiere = new Filiere();
        $filiere->setName($name);
        $this->getEntityManager()->persist($filiere);
        $this->getEntityManager()->flush();

        return $filiere;
    }
}
