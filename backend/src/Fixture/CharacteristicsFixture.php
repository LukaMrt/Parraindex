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
        $characteristic = new Characteristic()
            ->setType($this->getReference(CharacteristicTypesFixture::GITHUB, CharacteristicType::class))
            ->setPerson($this->getReference(PersonFixture::LUKA, Person::class))
            ->setValue('LukaMrt')
            ->setVisible(true);
        $manager->persist($characteristic);

        $characteristic2 = new Characteristic()
            ->setType($this->getReference(CharacteristicTypesFixture::INSTAGRAM, CharacteristicType::class))
            ->setPerson($this->getReference(PersonFixture::LUKA, Person::class))
            ->setValue('lukamrt')
            ->setVisible(false);
        $manager->persist($characteristic2);

        $characteristic3 = new Characteristic()
            ->setType($this->getReference(CharacteristicTypesFixture::EMAIL, CharacteristicType::class))
            ->setPerson($this->getReference(PersonFixture::LUKA, Person::class))
            ->setValue('maret.luka@gmail.com')
            ->setVisible(true);
        $manager->persist($characteristic3);

        $characteristic4 = new Characteristic()
            ->setType($this->getReference(CharacteristicTypesFixture::PHONE, CharacteristicType::class))
            ->setPerson($this->getReference(PersonFixture::GOD_CHILD_1, Person::class))
            ->setValue('+33 7 77 77 77 77')
            ->setVisible(true);
        $manager->persist($characteristic4);

        $manager->flush();
    }

    /**
     * @return string[]
     */
    #[\Override]
    public function getDependencies(): array
    {
        return [
            CharacteristicTypesFixture::class,
            PersonFixture::class,
        ];
    }
}
