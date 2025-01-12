<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contact\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contact>
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Contact::class);
    }

    public function create(Contact $contact): void
    {
        $this->update($contact);
    }

    /**
     * @return Contact[]
     */
    public function getAll(): array
    {
        return $this->findAll();
    }

    public function update(Contact $contact): void
    {
        $this->getEntityManager()->persist($contact);
        $this->getEntityManager()->flush();
    }
}
