<?php

declare(strict_types=1);

namespace App\Fixture;

use App\Entity\Person\Association;
use App\Entity\Person\Person;
use App\Entity\Person\PersonAssociation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PersonAssociationFixture extends Fixture implements DependentFixtureInterface
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        /** @var Association $bde */
        $bde = $this->getReference(AssociationFixture::BDE, Association::class);
        /** @var Association $bds */
        $bds = $this->getReference(AssociationFixture::BDS, Association::class);
        /** @var Association $juniorEntreprise */
        $juniorEntreprise = $this->getReference(AssociationFixture::JUNIOR_ENTREPRISE, Association::class);

        $entries = [
            [
                PersonFixture::LUKA,
                $bde,
                'Président',
                new \DateTimeImmutable('2022-09-01'),
                new \DateTimeImmutable('2023-06-30'),
            ],
            [
                PersonFixture::LUKA,
                $juniorEntreprise,
                'Membre',
                new \DateTimeImmutable('2022-10-01'),
                null,
            ],
            [
                PersonFixture::MELVYN,
                $bde,
                'Vice-Président',
                new \DateTimeImmutable('2022-09-01'),
                new \DateTimeImmutable('2023-06-30'),
            ],
            [
                PersonFixture::SARAH,
                $bds,
                'Secrétaire',
                new \DateTimeImmutable('2022-09-01'),
                new \DateTimeImmutable('2023-06-30'),
            ],
            [
                PersonFixture::VINCENT,
                $bds,
                'Trésorier',
                new \DateTimeImmutable('2022-09-01'),
                new \DateTimeImmutable('2023-06-30'),
            ],
        ];

        foreach ($entries as [$personRef, $association, $poste, $startDate, $endDate]) {
            $pa = new PersonAssociation()
                ->setPerson($this->getReference($personRef, Person::class))
                ->setAssociation($association)
                ->setPoste($poste)
                ->setStartDate($startDate)
                ->setEndDate($endDate);
            $manager->persist($pa);
        }

        $manager->flush();
    }

    #[\Override]
    public function getDependencies(): array
    {
        return [
            PersonFixture::class,
            AssociationFixture::class,
        ];
    }
}
