<?php

declare(strict_types=1);

namespace App\Fixture;

use App\Entity\Person\Person;
use App\Repository\PersonRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PersonFixture extends Fixture
{
    public const string LUKA        = 'person_luka';

    public const string MELVYN      = 'person_melvyn';

    public const string VINCENT     = 'person_vincent';

    public const string LILIAN      = 'person_lilian';

    public const string GOD_CHILD_1 = 'person_god_child_1';

    public const string GOD_CHILD_2 = 'person_god_child_2';

    public const string GOD_CHILD_3 = 'person_god_child_3';

    public const string GOD_FATHER  = 'person_god_father';

    #[\Override]
    public function load(ObjectManager $objectManager): void
    {
        $person = (new Person())
            ->setFirstName('Luka')
            ->setLastName('Maret')
            ->setDescription('Je suis Luka')
            ->setBiography('Je suis Luka')
            ->setColor('#0000FF')
            ->setStartYear(2021)
            ->setPicture('Luka.jpg')
            ->setCreatedAt(new \DateTimeImmutable());
        $objectManager->persist($person);
        $this->addReference(self::LUKA, $person);

        $melvyn = (new Person())
            ->setFirstName('Melvyn')
            ->setLastName('Delpree')
            ->setDescription('Je suis Melvyn')
            ->setBiography('Je suis Melvyn')
            ->setColor('#A52A2A')
            ->setStartYear(2021)
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTimeImmutable());
        $objectManager->persist($melvyn);
        $this->addReference(self::MELVYN, $melvyn);

        $vincent = (new Person())
            ->setFirstName('Vincent')
            ->setLastName('Chavot--Dambrun')
            ->setDescription('Je suis Vincent')
            ->setBiography('Je suis Vincent')
            ->setColor('#FF0000')
            ->setStartYear(2021)
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTimeImmutable());
        $objectManager->persist($vincent);
        $this->addReference(self::VINCENT, $vincent);

        $lilian = (new Person())
            ->setFirstName('Lilian')
            ->setLastName('Baudry')
            ->setDescription('Je suis Lilian')
            ->setBiography('Je suis Lilian')
            ->setColor('#0F0F0F')
            ->setStartYear(2021)
            ->setPicture('Lilian.jpg')
            ->setCreatedAt(new \DateTimeImmutable());
        $objectManager->persist($lilian);
        $this->addReference(self::LILIAN, $lilian);

        $godChild1 = (new Person())
            ->setFirstName('Godchild')
            ->setLastName('1')
            ->setDescription('Je suis fillot 1')
            ->setBiography('Je suis fillot 1')
            ->setColor('#0000FF')
            ->setStartYear(2022)
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTimeImmutable());
        $objectManager->persist($godChild1);
        $this->addReference(self::GOD_CHILD_1, $godChild1);

        $godChild2 = (new Person())
            ->setFirstName('Godchild')
            ->setLastName('2')
            ->setDescription('Je suis fillot 2')
            ->setBiography('Je suis fillot 2')
            ->setColor('#0000FF')
            ->setStartYear(2022)
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTimeImmutable());
        $objectManager->persist($godChild2);
        $this->addReference(self::GOD_CHILD_2, $godChild2);

        $godChild3 = (new Person())
            ->setFirstName('Godchild')
            ->setLastName('3')
            ->setDescription('Je suis fillot 3')
            ->setBiography('Je suis fillot 3')
            ->setColor('#0000FF')
            ->setStartYear(2022)
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTimeImmutable());
        $objectManager->persist($godChild3);
        $this->addReference(self::GOD_CHILD_3, $godChild3);

        $godFather = (new Person())
            ->setFirstName('Godfather')
            ->setLastName('1')
            ->setDescription('Je suis parrain 1')
            ->setBiography('Je suis parrain 1')
            ->setColor('#0000FF')
            ->setStartYear(2020)
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTimeImmutable());
        $objectManager->persist($godFather);
        $this->addReference(self::GOD_FATHER, $godFather);

        $objectManager->flush();
    }
}
