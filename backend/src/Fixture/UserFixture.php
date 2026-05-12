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
        $accounts = [
            // [personRef, email, roles, picture, password]
            [
                PersonFixture::LUKA,
                'luka.maret@etu.univ-lyon1.fr',
                [
                    Role::ADMIN,
                    Role::USER,
                ],
                'Luka.jpg',
                'password',
            ],
            [
                PersonFixture::LILIAN,
                'lilian.baudry@etu.univ-lyon1.fr',
                [Role::USER],
                'Lilian.jpg',
                'password',
            ],
            [
                PersonFixture::VINCENT,
                'vincent.chavot-dambrun@etu.univ-lyon1.fr',
                [Role::USER],
                null,
                'password',
            ],
            [
                PersonFixture::SARAH,
                'sarah.fontaine@etu.univ-lyon1.fr',
                [Role::USER],
                null,
                'password',
            ],
            [
                PersonFixture::EMMA,
                'emma.girard@etu.univ-lyon1.fr',
                [Role::USER],
                null,
                'password',
            ],
        ];

        foreach ($accounts as [$personRef, $email, $roles, $picture, $password]) {
            /** @var Person $person */
            $person = $this->getReference($personRef, Person::class);

            $user = new User();
            $user->setEmail($email)
                ->setPerson($person)
                ->setRoles($roles)
                ->setPicture($picture)
                ->setValidated(true)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setPassword($this->passwordHasher->hashPassword($user, $password));

            $manager->persist($user);
        }

        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [PersonFixture::class];
    }
}
