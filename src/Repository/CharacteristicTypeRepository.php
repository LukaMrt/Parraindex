<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Characteristic\CharacteristicType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CharacteristicType>
 */
class CharacteristicTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, CharacteristicType::class);
    }

    public function update(CharacteristicType $characteristicType): void
    {
        $this->getEntityManager()->persist($characteristicType);
        $this->getEntityManager()->flush();
    }

    public function getAll(): array
    {
        return $this->findAll();
    }
}
