<?php

declare(strict_types=1);

namespace App\Fixture;

use App\Entity\Characteristic\CharacteristicType;
use App\Entity\Characteristic\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CharacteristicTypesFixture extends Fixture
{
    public const string GITHUB    = 'characteristic_type_github';

    public const string INSTAGRAM = 'characteristic_type_instagram';

    public const string EMAIL     = 'characteristic_type_email';

    public const string PHONE     = 'characteristic_type_phone';

    #[\Override]
    public function load(ObjectManager $objectManager): void
    {
        $characteristicType = (new CharacteristicType())
            ->setType(Type::URL)
            ->setTitle('Github')
            ->setImage('github.svg')
            ->setUrl('https://github.com/')
            ->setPlace(0);
        $objectManager->persist($characteristicType);
        $this->addReference(self::GITHUB, $characteristicType);

        $type2 = (new CharacteristicType())
            ->setType(Type::URL)
            ->setTitle('Instagram')
            ->setImage('instagram.svg')
            ->setUrl('https://www.instagram.com/')
            ->setPlace(1);
        $objectManager->persist($type2);
        $this->addReference(self::INSTAGRAM, $type2);

        $type3 = (new CharacteristicType())
            ->setType(Type::EMAIL)
            ->setTitle('Email')
            ->setImage('mail.svg')
            ->setUrl('mailto:')
            ->setPlace(2);
        $objectManager->persist($type3);
        $this->addReference(self::EMAIL, $type3);

        $type4 = (new CharacteristicType())
            ->setType(Type::PHONE)
            ->setTitle('Phone')
            ->setImage('0738383838')
            ->setUrl('tel:')
            ->setPlace(3);
        $objectManager->persist($type4);
        $this->addReference(self::PHONE, $type4);

        $objectManager->flush();
    }
}
