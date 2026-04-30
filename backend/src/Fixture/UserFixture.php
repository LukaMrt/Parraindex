<?php

declare(strict_types=1);

namespace App\Fixture;

use App\Entity\Person\Person;
use App\Entity\Person\Role;
use App\Entity\Person\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        /** @var Person $person */
        $person = $this->getReference(PersonFixture::LUKA, Person::class);
        $user   = new User()
            ->setEmail('luka.maret@etu.univ-lyon1.fr')
            ->setPerson($person)
            ->setRoles([Role::ADMIN, Role::USER])
            ->setPicture('Luka.jpg')
            ->setVerified(true)
            ->setCreatedAt(new \DateTimeImmutable());

        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));

        $manager->persist($user);

        /** @var Person $person */
        $person = $this->getReference(PersonFixture::LILIAN, Person::class);
        $lilian = new User()
            ->setEmail('lilian.baudry@etu.univ-lyon1.fr')
            ->setPerson($person)
            ->setRoles([Role::USER])
            ->setPicture('Lilian.jpg')
            ->setVerified(true)
            ->setCreatedAt(new \DateTimeImmutable());

        $lilian->setPassword($this->passwordHasher->hashPassword($lilian, 'password2'));

        $manager->persist($lilian);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PersonFixture::class,
        ];
    }
}
