<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Person\Association;
use App\Entity\Person\Filiere;
use App\Entity\Person\School;
use Doctrine\ORM\EntityManagerInterface;

final readonly class MergeService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * Réassigne tous les PersonFiliere de $source vers $target puis supprime $source.
     */
    public function mergeFiliere(Filiere $source, Filiere $target): int
    {
        return $this->doMerge(
            'UPDATE App\Entity\Person\PersonFiliere pf SET pf.filiere = :target WHERE pf.filiere = :source',
            $source,
            $target,
        );
    }

    /**
     * Réassigne tous les PersonAssociation de $source vers $target puis supprime $source.
     */
    public function mergeAssociation(Association $source, Association $target): int
    {
        return $this->doMerge(
            'UPDATE App\Entity\Person\PersonAssociation pa SET pa.association = :target WHERE pa.association = :source',
            $source,
            $target,
        );
    }

    /**
     * Réassigne tous les PersonFiliere (school) de $source vers $target puis supprime $source.
     */
    public function mergeSchool(School $source, School $target): int
    {
        return $this->doMerge(
            'UPDATE App\Entity\Person\PersonFiliere pf SET pf.school = :target WHERE pf.school = :source',
            $source,
            $target,
        );
    }

    private function doMerge(string $dql, object $source, object $target): int
    {
        $conn = $this->em->getConnection();
        $conn->beginTransaction();

        try {
            $raw = $this->em->createQuery($dql)
                ->setParameter('target', $target)
                ->setParameter('source', $source)
                ->execute();

            $count = is_int($raw) ? $raw : 0;

            $this->em->remove($source);
            $this->em->flush();

            $conn->commit();
        } catch (\Throwable $throwable) {
            $conn->rollBack();
            throw $throwable;
        }

        return $count;
    }
}
