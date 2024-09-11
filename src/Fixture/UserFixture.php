<?php

namespace App\Fixture;

use App\Entity\Person\Role;
use App\Entity\Person\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $luka = (new User())
            ->setEmail('luka@luka.com')
            ->setPassword('password')
            ->setPerson($this->getReference(PersonFixture::LUKA))
            ->setRoles(new ArrayCollection([Role::ADMIN, Role::USER]))
            ->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($luka);

        $melvyn = (new User())
            ->setEmail('melvyn@melvyn.fr')
            ->setPassword('password2')
            ->setPerson($this->getReference(PersonFixture::MELVYN))
            ->setRoles(new ArrayCollection([Role::USER]))
            ->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($melvyn);

        $manager->flush();
    }
}
