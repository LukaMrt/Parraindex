<?php

declare(strict_types=1);

namespace App\Fixture;

use App\Entity\Person\School;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SchoolFixture extends Fixture
{
    public const string IUT_LYON1 = 'school_iut_lyon1';

    public const string INSA_LYON = 'school_insa_lyon';

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $iut = new School()
            ->setName('IUT Lyon 1')
            ->setLogo('iut-lyon1.jpg');
        $manager->persist($iut);
        $this->addReference(self::IUT_LYON1, $iut);

        $insa = new School()
            ->setName('INSA Lyon')
            ->setLogo('insa-lyon.png');
        $manager->persist($insa);
        $this->addReference(self::INSA_LYON, $insa);

        $manager->flush();
    }
}
