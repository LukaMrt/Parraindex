<?php

declare(strict_types=1);

namespace App\Fixture;

use App\Entity\Person\Person;
use App\Entity\Person\Filiere;
use App\Entity\Person\PersonFiliere;
use App\Entity\Person\School;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PersonFiliereFixture extends Fixture implements DependentFixtureInterface
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        /** @var Filiere $informatique */
        $informatique = $this->getReference(FiliereFixture::INFORMATIQUE, Filiere::class);
        /** @var Filiere $reseaux */
        $reseaux = $this->getReference(FiliereFixture::RESEAUX, Filiere::class);
        /** @var School $iut */
        $iut = $this->getReference(SchoolFixture::IUT_LYON1, School::class);
        /** @var School $insa */
        $insa = $this->getReference(SchoolFixture::INSA_LYON, School::class);

        $entries = [
            [
                PersonFixture::HENRI,
                $informatique,
                $iut,
                2019,
                2022,
            ],
            [
                PersonFixture::CAMILLE,
                $informatique,
                $iut,
                2019,
                2022,
            ],
            [
                PersonFixture::BAPTISTE,
                $informatique,
                $iut,
                2019,
                2022,
            ],
            [
                PersonFixture::LILIAN,
                $informatique,
                $iut,
                2020,
                2023,
            ],
            [
                PersonFixture::LILIAN,
                $informatique,
                $insa,
                2023,
                null,
            ],
            [
                PersonFixture::MARINE,
                $informatique,
                $iut,
                2020,
                2023,
            ],
            [
                PersonFixture::THOMAS,
                $reseaux,
                $iut,
                2020,
                2023,
            ],
            [
                PersonFixture::PAULINE,
                $informatique,
                $iut,
                2020,
                2023,
            ],
            [
                PersonFixture::LUKA,
                $informatique,
                $iut,
                2021,
                2024,
            ],
            [
                PersonFixture::LUKA,
                $informatique,
                $insa,
                2024,
                null,
            ],
            [
                PersonFixture::MELVYN,
                $informatique,
                $iut,
                2021,
                2024,
            ],
            [
                PersonFixture::VINCENT,
                $informatique,
                $iut,
                2021,
                2024,
            ],
            [
                PersonFixture::SARAH,
                $informatique,
                $iut,
                2021,
                2024,
            ],
            [
                PersonFixture::JULIAN,
                $reseaux,
                $iut,
                2021,
                2024,
            ],
            [
                PersonFixture::EMMA,
                $informatique,
                $iut,
                2022,
                2025,
            ],
            [
                PersonFixture::ROMAIN,
                $informatique,
                $iut,
                2022,
                2025,
            ],
            [
                PersonFixture::CLARA,
                $informatique,
                $iut,
                2022,
                2025,
            ],
            [
                PersonFixture::MAXIME,
                $informatique,
                $iut,
                2022,
                2025,
            ],
            [
                PersonFixture::ZOE,
                $informatique,
                $iut,
                2023,
                null,
            ],
            [
                PersonFixture::LUCAS,
                $informatique,
                $iut,
                2023,
                null,
            ],
            [
                PersonFixture::INES,
                $informatique,
                $iut,
                2023,
                null,
            ],
            [
                PersonFixture::THEO,
                $informatique,
                $iut,
                2023,
                null,
            ],
            [
                PersonFixture::MANON,
                $informatique,
                $iut,
                2023,
                null,
            ],
        ];

        foreach ($entries as [$personRef, $filiere, $school, $startYear, $endYear]) {
            $pf = new PersonFiliere()
                ->setPerson($this->getReference($personRef, Person::class))
                ->setFiliere($filiere)
                ->setSchool($school)
                ->setStartYear($startYear)
                ->setEndYear($endYear);
            $manager->persist($pf);
        }

        $manager->flush();
    }

    #[\Override]
    public function getDependencies(): array
    {
        return [
            PersonFixture::class,
            FiliereFixture::class,
            SchoolFixture::class,
        ];
    }
}
