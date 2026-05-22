<?php

declare(strict_types=1);

namespace App\Fixture;

use App\Entity\Person\Person;
use App\Entity\Person\PersonLink;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PersonLinkFixture extends Fixture implements DependentFixtureInterface
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $data = [
            // [personRef, title, url]
            [
                PersonFixture::HENRI,
                'Portfolio',
                'https://henridurand.dev',
            ],
            [
                PersonFixture::LUKA,
                'Blog',
                'https://lukamaret.fr',
            ],
            [
                PersonFixture::LUKA,
                'CV',
                'https://cv.lukamaret.fr',
            ],
            [
                PersonFixture::SARAH,
                'Portfolio',
                'https://sarahfontaine.io',
            ],
            [
                PersonFixture::ROMAIN,
                'Recherches',
                'https://romain-ai.github.io',
            ],
            [
                PersonFixture::CLARA,
                'Projet open source',
                'https://github.com/ClaraMartin/ecodev',
            ],
            [
                PersonFixture::MAXIME,
                'Site perso',
                'https://maxime-dubois.xyz',
            ],
        ];

        foreach ($data as [$personRef, $title, $url]) {
            $link = new PersonLink()
                ->setPerson($this->getReference($personRef, Person::class))
                ->setTitle($title)
                ->setUrl($url);
            $manager->persist($link);
        }

        $manager->flush();
    }

    /**
     * @return string[]
     */
    #[\Override]
    public function getDependencies(): array
    {
        return [PersonFixture::class];
    }
}
