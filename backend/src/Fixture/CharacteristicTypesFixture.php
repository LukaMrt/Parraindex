<?php

declare(strict_types=1);

namespace App\Fixture;

use App\Entity\Characteristic\CharacteristicType;
use App\Entity\Characteristic\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CharacteristicTypesFixture extends Fixture
{
    public const string GITHUB = 'characteristic_type_github';

    public const string INSTAGRAM = 'characteristic_type_instagram';

    public const string EMAIL = 'characteristic_type_email';

    public const string PHONE = 'characteristic_type_phone';

    public const string LINKEDIN = 'characteristic_type_linkedin';

    public const string TWITTER = 'characteristic_type_twitter';

    public const string DISCORD = 'characteristic_type_discord';

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $github = new CharacteristicType()
            ->setType(Type::URL)
            ->setTitle('Github')
            ->setImage('github.svg')
            ->setUrl('https://github.com/')
            ->setPlace(0);
        $manager->persist($github);
        $this->addReference(self::GITHUB, $github);

        $instagram = new CharacteristicType()
            ->setType(Type::URL)
            ->setTitle('Instagram')
            ->setImage('instagram.svg')
            ->setUrl('https://www.instagram.com/')
            ->setPlace(1);
        $manager->persist($instagram);
        $this->addReference(self::INSTAGRAM, $instagram);

        $email = new CharacteristicType()
            ->setType(Type::EMAIL)
            ->setTitle('Email')
            ->setImage('mail.svg')
            ->setUrl('mailto:')
            ->setPlace(2);
        $manager->persist($email);
        $this->addReference(self::EMAIL, $email);

        $phone = new CharacteristicType()
            ->setType(Type::PHONE)
            ->setTitle('Phone')
            ->setImage('telephone.svg')
            ->setUrl('tel:')
            ->setPlace(3);
        $manager->persist($phone);
        $this->addReference(self::PHONE, $phone);

        $linkedin = new CharacteristicType()
            ->setType(Type::URL)
            ->setTitle('LinkedIn')
            ->setImage('linkedin.svg')
            ->setUrl('https://www.linkedin.com/in/')
            ->setPlace(4);
        $manager->persist($linkedin);
        $this->addReference(self::LINKEDIN, $linkedin);

        $twitter = new CharacteristicType()
            ->setType(Type::URL)
            ->setTitle('Twitter')
            ->setImage('twitter.svg')
            ->setUrl('https://twitter.com/')
            ->setPlace(5);
        $manager->persist($twitter);
        $this->addReference(self::TWITTER, $twitter);

        $discord = new CharacteristicType()
            ->setType(Type::URL)
            ->setTitle('Discord')
            ->setImage('discord.svg')
            ->setUrl('https://discord.com/users/')
            ->setPlace(6);
        $manager->persist($discord);
        $this->addReference(self::DISCORD, $discord);

        $manager->flush();
    }
}
