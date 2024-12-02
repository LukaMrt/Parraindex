<?php

declare(strict_types=1);

namespace App\Fixture;

use App\Entity\Characteristic\Characteristic;
use App\Entity\Characteristic\CharacteristicType;
use App\Entity\Person\Person;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CharacteristicsFixture extends Fixture implements DependentFixtureInterface
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $characteristic = (new Characteristic())
            ->setType($this->getCharacteristicType(CharacteristicTypesFixture::GITHUB))
            ->setPerson($this->getPerson(PersonFixture::LUKA))
            ->setValue('LukaMrt')
            ->setVisible(true);
        $manager->persist($characteristic);

        $characteristic2 = (new Characteristic())
            ->setType($this->getCharacteristicType(CharacteristicTypesFixture::INSTAGRAM))
            ->setPerson($this->getPerson(PersonFixture::LUKA))
            ->setValue('lukamrt')
            ->setVisible(false);
        $manager->persist($characteristic2);

        $characteristic3 = (new Characteristic())
            ->setType($this->getCharacteristicType(CharacteristicTypesFixture::EMAIL))
            ->setPerson($this->getPerson(PersonFixture::LUKA))
            ->setValue('maret.luka@gmail.com')
            ->setVisible(true);
        $manager->persist($characteristic3);

        $manager->flush();
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

    private function getCharacteristicType(string $name): CharacteristicType
    {
        /** @var CharacteristicType $reference */
        $reference = $this->getReference($name, CharacteristicTypesFixture::class);
        return $reference;
    }

    private function getPerson(string $name): Person
    {
        /** @var Person $reference */
        $reference = $this->getReference($name, PersonFixture::class);
        return $reference;
    }
}
