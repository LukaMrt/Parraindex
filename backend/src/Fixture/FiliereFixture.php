<?php

declare(strict_types=1);

namespace App\Fixture;

use App\Entity\Person\Filiere;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FiliereFixture extends Fixture
{
    public const string INFORMATIQUE = 'filiere_informatique';

    public const string RESEAUX = 'filiere_reseaux';

    public const string GENIE_ELECTRIQUE = 'filiere_genie_electrique';

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $informatique = new Filiere()
            ->setName('Informatique')
            ->setColor('#3B82F6');
        $manager->persist($informatique);
        $this->addReference(self::INFORMATIQUE, $informatique);

        $reseaux = new Filiere()
            ->setName('Réseaux & Télécommunications')
            ->setColor('#10B981');
        $manager->persist($reseaux);
        $this->addReference(self::RESEAUX, $reseaux);

        $genieElectrique = new Filiere()
            ->setName('Génie Électrique')
            ->setColor('#F59E0B');
        $manager->persist($genieElectrique);
        $this->addReference(self::GENIE_ELECTRIQUE, $genieElectrique);

        $manager->flush();
    }
}
