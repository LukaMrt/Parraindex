<?php

declare(strict_types=1);

namespace App\Fixture;

use App\Entity\Person\Person;
use App\Entity\Person\Role;
use App\Entity\Person\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        /** @var Person $person */
        $person = $this->getReference(PersonFixture::LUKA);
        $user   = (new User())
            ->setEmail('luka@luka.com')
            ->setPassword('password')
            ->setPerson($person)
            ->setRoles(new ArrayCollection([Role::ADMIN, Role::USER]))
            ->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($user);

        /** @var Person $person */
        $person = $this->getReference(PersonFixture::MELVYN);
        $melvyn = (new User())
            ->setEmail('melvyn@melvyn.fr')
            ->setPassword('password2')
            ->setPerson($person)
            ->setRoles(new ArrayCollection([Role::USER]))
            ->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($melvyn);

        $manager->flush();
    }
}
