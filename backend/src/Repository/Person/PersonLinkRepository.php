<?php

declare(strict_types=1);

namespace App\Repository\Person;

use App\Entity\Person\PersonLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PersonLink>
 */
class PersonLinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, PersonLink::class);
    }
}
