<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Person\Person;
use App\Entity\Sponsor\Sponsor;
use App\Entity\Sponsor\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sponsor>
 */
class SponsorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Sponsor::class);
    }

    public function getById(int $id): ?Sponsor
    {
        return $this->find($id);
    }

    public function getByPeopleIds(int $gofFatherId, int $godChildId): ?Sponsor
    {
        return $this->findOneBy([
            'godFather' => $gofFatherId,
            'godChild' => $godChildId
        ]);
    }

    public function create(Sponsor $sponsor): void
    {
        $this->update($sponsor);
    }

    public function update(Sponsor $sponsor): void
    {
        if (!$sponsor->getCreatedAt() instanceof \DateTimeInterface) {
            $sponsor->setCreatedAt(new \DateTime());
        }

        if (!$sponsor->getType() instanceof Type) {
            $sponsor->setType(Type::UNKNOWN);
        }

        $this->getEntityManager()->persist($sponsor);
        $this->getEntityManager()->flush();
    }

    public function delete(Sponsor $sponsor): void
    {
        $this->getEntityManager()->remove($sponsor);
        $this->getEntityManager()->flush();
    }
}
