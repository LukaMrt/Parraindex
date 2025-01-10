<?php

declare(strict_types=1);

namespace App\Fixture;

use App\Entity\Person\Person;
use App\Entity\Sponsor\Sponsor;
use App\Entity\Sponsor\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SponsorFixture extends Fixture implements DependentFixtureInterface
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        /** @var Person $person */
        $person = $this->getReference(PersonFixture::LUKA, PersonFixture::class);
        /** @var Person $person2 */
        $person2  = $this->getReference(PersonFixture::GOD_CHILD_1, PersonFixture::class);
        $sponsor1 = new Sponsor()
            ->setGodFather($person)
            ->setGodChild($person2)
            ->setDate(new \DateTime('2022-09-01'))
            ->setType(Type::CLASSIC)
            ->setDescription('God child 1 a demandé Luka dans son formulaire de parrainage')
            ->setCreatedAt(new \DateTime());
        $manager->persist($sponsor1);

        /** @var Person $person */
        $person = $this->getReference(PersonFixture::LUKA, PersonFixture::class);
        /** @var Person $person2 */
        $person2  = $this->getReference(PersonFixture::GOD_CHILD_2, PersonFixture::class);
        $sponsor2 = new Sponsor()
            ->setGodFather($person)
            ->setGodChild($person2)
            ->setDate(new \DateTime('2022-09-01'))
            ->setType(Type::CLASSIC)
            ->setDescription('Luka a choisi God child 2')
            ->setCreatedAt(new \DateTime());
        $manager->persist($sponsor2);

        /** @var Person $person */
        $person = $this->getReference(PersonFixture::LUKA, PersonFixture::class);
        /** @var Person $person2 */
        $person2  = $this->getReference(PersonFixture::GOD_CHILD_3, PersonFixture::class);
        $sponsor3 = new Sponsor()
            ->setGodFather($person)
            ->setGodChild($person2)
            ->setDate(new \DateTime('2024-03-21'))
            ->setType(Type::HEART)
            ->setDescription('God child 3 a demandé Luka en parrain pendant une soirée')
            ->setCreatedAt(new \DateTime());
        $manager->persist($sponsor3);

        /** @var Person $person */
        $person = $this->getReference(PersonFixture::GOD_FATHER, PersonFixture::class);
        /** @var Person $person2 */
        $person2  = $this->getReference(PersonFixture::LUKA, PersonFixture::class);
        $sponsor4 = new Sponsor()
            ->setGodFather($person)
            ->setGodChild($person2)
            ->setDate(new \DateTime('2021-09-01'))
            ->setType(Type::CLASSIC)
            ->setDescription('God father a choisi Luka')
            ->setCreatedAt(new \DateTime());
        $manager->persist($sponsor4);

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
        ];
    }
}
