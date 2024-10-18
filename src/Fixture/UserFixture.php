<?php

declare(strict_types=1);

namespace App\Fixture;

use App\Entity\Person\Role;
use App\Entity\Person\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    #[\Override]
    public function load(ObjectManager $objectManager): void
    {
        $user = (new User())
            ->setEmail('luka@luka.com')
            ->setPassword('password')
            ->setPerson($this->getReference(PersonFixture::LUKA))
            ->setRoles(new ArrayCollection([Role::ADMIN, Role::USER]))
            ->setCreatedAt(new \DateTimeImmutable());
        $objectManager->persist($user);

        $melvyn = (new User())
            ->setEmail('melvyn@melvyn.fr')
            ->setPassword('password2')
            ->setPerson($this->getReference(PersonFixture::MELVYN))
            ->setRoles(new ArrayCollection([Role::USER]))
            ->setCreatedAt(new \DateTimeImmutable());
        $objectManager->persist($melvyn);

        $objectManager->flush();
    }
}
