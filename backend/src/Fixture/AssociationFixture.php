<?php

declare(strict_types=1);

namespace App\Fixture;

use App\Entity\Person\Association;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AssociationFixture extends Fixture
{
    public const string BDE = 'association_bde';

    public const string BDS = 'association_bds';

    public const string JUNIOR_ENTREPRISE = 'association_junior_entreprise';

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $bde = new Association()
            ->setName('BDE');
        $manager->persist($bde);
        $this->addReference(self::BDE, $bde);

        $bds = new Association()
            ->setName('BDS');
        $manager->persist($bds);
        $this->addReference(self::BDS, $bds);

        $juniorEntreprise = new Association()
            ->setName('Junior Entreprise');
        $manager->persist($juniorEntreprise);
        $this->addReference(self::JUNIOR_ENTREPRISE, $juniorEntreprise);

        $manager->flush();
    }
}
