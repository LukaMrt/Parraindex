<?php

namespace App\Fixture;

use App\Entity\Characteristic\Characteristic;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CharacteristicsFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $characteristic = (new Characteristic())
            ->setType($this->getReference(CharacteristicTypesFixture::GITHUB))
            ->setPerson($this->getReference(PersonFixture::LUKA))
            ->setValue('LukaMrt')
            ->setVisible(true);
        $manager->persist($characteristic);

        $characteristic2 = (new Characteristic())
            ->setType($this->getReference(CharacteristicTypesFixture::INSTAGRAM))
            ->setPerson($this->getReference(PersonFixture::LUKA))
            ->setValue('lukamrt')
            ->setVisible(false);
        $manager->persist($characteristic2);

        $characteristic3 = (new Characteristic())
            ->setType($this->getReference(CharacteristicTypesFixture::EMAIL))
            ->setPerson($this->getReference(PersonFixture::LUKA))
            ->setValue('maret.luka@gmail.com')
            ->setVisible(true);
        $manager->persist($characteristic3);

        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            PersonFixture::class,
            CharacteristicTypesFixture::class,
        ];
    }
}
