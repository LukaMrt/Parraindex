<?php

namespace App\Repository;

use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Person>
 */
class PersonRepository extends ServiceEntityRepository
{
    const string DEFAULT_PICTURE = 'no-picture.svg';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    /**
     * @return Person[]
     */
    public function getAll(): array
    {
        return $this->createQueryBuilder('p')->getQuery()->getResult();
    }

    public function getByIdentity(string $firstName, string $lastName): ?Person
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.firstName = :firstName')
            ->andWhere('p.lastName = :lastName')
            ->setParameter('firstName', $firstName)
            ->setParameter('lastName', $lastName)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getById(int $id): ?Person
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getByEmail(string $email): ?Person
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getAllIdentities(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.firstName', 'p.lastName')
            ->getQuery()
            ->getResult();
    }

    public function update(Person $person): void
    {
        if ($person->getCreatedAt() === null) {
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
