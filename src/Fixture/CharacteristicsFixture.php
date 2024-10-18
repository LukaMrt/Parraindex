<?php

declare(strict_types=1);

namespace App\Fixture;

use App\Entity\Characteristic\Characteristic;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CharacteristicsFixture extends Fixture implements DependentFixtureInterface
{
    #[\Override]
    public function load(ObjectManager $objectManager): void
    {
        $characteristic = (new Characteristic())
            ->setType($this->getReference(CharacteristicTypesFixture::GITHUB))
            ->setPerson($this->getReference(PersonFixture::LUKA))
            ->setValue('LukaMrt')
            ->setVisible(true);
        $objectManager->persist($characteristic);

        $characteristic2 = (new Characteristic())
            ->setType($this->getReference(CharacteristicTypesFixture::INSTAGRAM))
            ->setPerson($this->getReference(PersonFixture::LUKA))
            ->setValue('lukamrt')
            ->setVisible(false);
        $objectManager->persist($characteristic2);

        $characteristic3 = (new Characteristic())
            ->setType($this->getReference(CharacteristicTypesFixture::EMAIL))
            ->setPerson($this->getReference(PersonFixture::LUKA))
            ->setValue('maret.luka@gmail.com')
            ->setVisible(true);
        $objectManager->persist($characteristic3);

        $objectManager->flush();
    }

    /**
     * @return string[]
     */
    #[\Override]
    public function getDependencies(): array
    {
        return [
            PersonFixture::class,
            CharacteristicTypesFixture::class,
        ];
    }
}
