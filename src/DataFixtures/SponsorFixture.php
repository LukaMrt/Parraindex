<?php

namespace App\DataFixtures;

use App\Entity\Sponsor;
use App\Entity\SponsorType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SponsorFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $sponsor1 = (new Sponsor())
            ->setGodFather($this->getReference(PersonFixture::LUKA))
            ->setGodChild($this->getReference(PersonFixture::GOD_CHILD_1))
            ->setDate(new \DateTimeImmutable('2022-09-01'))
            ->setType(SponsorType::CLASSIC)
            ->setDescription('God child 1 a demandé Luka dans son formulaire de parrainage')
            ->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($sponsor1);

        $sponsor2 = (new Sponsor())
            ->setGodFather($this->getReference(PersonFixture::LUKA))
            ->setGodChild($this->getReference(PersonFixture::GOD_CHILD_2))
            ->setDate(new \DateTimeImmutable('2022-09-01'))
            ->setType(SponsorType::CLASSIC)
            ->setDescription('Luka a choisi God child 2')
            ->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($sponsor2);

        $sponsor3 = (new Sponsor())
            ->setGodFather($this->getReference(PersonFixture::LUKA))
            ->setGodChild($this->getReference(PersonFixture::GOD_CHILD_3))
            ->setDate(new \DateTimeImmutable('2024-03-21'))
            ->setType(SponsorType::HEART)
            ->setDescription('God child 3 a demandé Luka en parrain pendant une soirée')
            ->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($sponsor3);

        $sponsor4 = (new Sponsor())
            ->setGodFather($this->getReference(PersonFixture::GOD_FATHER))
            ->setGodChild($this->getReference(PersonFixture::LUKA))
            ->setDate(new \DateTimeImmutable('2021-09-01'))
            ->setType(SponsorType::CLASSIC)
            ->setDescription('God father a choisi Luka')
            ->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($sponsor4);

        $manager->flush();
    }
}
