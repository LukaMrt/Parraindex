<?php

declare(strict_types=1);

namespace App\Fixture;

use App\Entity\Characteristic\Characteristic;
use App\Entity\Characteristic\CharacteristicType;
use App\Entity\Person\Person;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CharacteristicsFixture extends Fixture implements DependentFixtureInterface
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $data = [
            // [personRef, typeRef, value, visible]

            // ── Henri Durand ─────────────────────────────────────────────────
            [
                PersonFixture::HENRI,
                CharacteristicTypesFixture::GITHUB,
                'HenriDurand',
                true,
            ],
            [
                PersonFixture::HENRI,
                CharacteristicTypesFixture::EMAIL,
                'henri.durand@etu.univ-lyon1.fr',
                true,
            ],
            [
                PersonFixture::HENRI,
                CharacteristicTypesFixture::LINKEDIN,
                'henri-durand',
                true,
            ],

            // ── Camille Leclerc ──────────────────────────────────────────────
            [
                PersonFixture::CAMILLE,
                CharacteristicTypesFixture::GITHUB,
                'CamilleDev',
                true,
            ],
            [
                PersonFixture::CAMILLE,
                CharacteristicTypesFixture::INSTAGRAM,
                'camille.dev',
                false,
            ],
            [
                PersonFixture::CAMILLE,
                CharacteristicTypesFixture::EMAIL,
                'camille.leclerc@etu.univ-lyon1.fr',
                true,
            ],
            [
                PersonFixture::CAMILLE,
                CharacteristicTypesFixture::LINKEDIN,
                'camille-leclerc-dev',
                true,
            ],

            // ── Baptiste Moreau ──────────────────────────────────────────────
            [
                PersonFixture::BAPTISTE,
                CharacteristicTypesFixture::GITHUB,
                'BaptisteMoreau',
                true,
            ],
            [
                PersonFixture::BAPTISTE,
                CharacteristicTypesFixture::DISCORD,
                'baptiste#4242',
                false,
            ],
            [
                PersonFixture::BAPTISTE,
                CharacteristicTypesFixture::EMAIL,
                'baptiste.moreau@etu.univ-lyon1.fr',
                true,
            ],

            // ── Lilian Baudry ────────────────────────────────────────────────
            [
                PersonFixture::LILIAN,
                CharacteristicTypesFixture::GITHUB,
                'LilianBaudry',
                true,
            ],
            [
                PersonFixture::LILIAN,
                CharacteristicTypesFixture::EMAIL,
                'lilian.baudry@etu.univ-lyon1.fr',
                true,
            ],
            [
                PersonFixture::LILIAN,
                CharacteristicTypesFixture::PHONE,
                '+33 6 12 34 56 78',
                false,
            ],
            [
                PersonFixture::LILIAN,
                CharacteristicTypesFixture::LINKEDIN,
                'lilian-baudry',
                true,
            ],

            // ── Marine Petit ────────────────────────────────────────────────
            [
                PersonFixture::MARINE,
                CharacteristicTypesFixture::GITHUB,
                'MarinePetit',
                true,
            ],
            [
                PersonFixture::MARINE,
                CharacteristicTypesFixture::INSTAGRAM,
                'marine.data',
                true,
            ],
            [
                PersonFixture::MARINE,
                CharacteristicTypesFixture::EMAIL,
                'marine.petit@etu.univ-lyon1.fr',
                true,
            ],

            // ── Thomas Bernard ───────────────────────────────────────────────
            [
                PersonFixture::THOMAS,
                CharacteristicTypesFixture::GITHUB,
                'ThomasBernard-IUT',
                true,
            ],
            [
                PersonFixture::THOMAS,
                CharacteristicTypesFixture::EMAIL,
                'thomas.bernard@etu.univ-lyon1.fr',
                true,
            ],
            [
                PersonFixture::THOMAS,
                CharacteristicTypesFixture::DISCORD,
                'thomas_net#0001',
                false,
            ],

            // ── Pauline Simon ────────────────────────────────────────────────
            [
                PersonFixture::PAULINE,
                CharacteristicTypesFixture::GITHUB,
                'PaulineSimon',
                true,
            ],
            [
                PersonFixture::PAULINE,
                CharacteristicTypesFixture::INSTAGRAM,
                'pauline.ux',
                true,
            ],
            [
                PersonFixture::PAULINE,
                CharacteristicTypesFixture::EMAIL,
                'pauline.simon@etu.univ-lyon1.fr',
                true,
            ],
            [
                PersonFixture::PAULINE,
                CharacteristicTypesFixture::LINKEDIN,
                'pauline-simon-ux',
                true,
            ],

            // ── Luka Maret ───────────────────────────────────────────────────
            [
                PersonFixture::LUKA,
                CharacteristicTypesFixture::GITHUB,
                'LukaMrt',
                true,
            ],
            [
                PersonFixture::LUKA,
                CharacteristicTypesFixture::INSTAGRAM,
                'lukamrt',
                false,
            ],
            [
                PersonFixture::LUKA,
                CharacteristicTypesFixture::EMAIL,
                'maret.luka@gmail.com',
                true,
            ],
            [
                PersonFixture::LUKA,
                CharacteristicTypesFixture::LINKEDIN,
                'luka-maret',
                true,
            ],
            [
                PersonFixture::LUKA,
                CharacteristicTypesFixture::TWITTER,
                'LukaMrt',
                true,
            ],

            // ── Melvyn Delpree ───────────────────────────────────────────────
            [
                PersonFixture::MELVYN,
                CharacteristicTypesFixture::GITHUB,
                'MelvynDelpree',
                true,
            ],
            [
                PersonFixture::MELVYN,
                CharacteristicTypesFixture::DISCORD,
                'melvyn_ctf#1337',
                true,
            ],
            [
                PersonFixture::MELVYN,
                CharacteristicTypesFixture::EMAIL,
                'melvyn.delpree@etu.univ-lyon1.fr',
                true,
            ],
            [
                PersonFixture::MELVYN,
                CharacteristicTypesFixture::TWITTER,
                'MelvynSec',
                false,
            ],

            // ── Vincent Chavot--Dambrun ──────────────────────────────────────
            [
                PersonFixture::VINCENT,
                CharacteristicTypesFixture::GITHUB,
                'VincentChavot',
                true,
            ],
            [
                PersonFixture::VINCENT,
                CharacteristicTypesFixture::EMAIL,
                'vincent.chavot-dambrun@etu.univ-lyon1.fr',
                true,
            ],
            [
                PersonFixture::VINCENT,
                CharacteristicTypesFixture::LINKEDIN,
                'vincent-chavot-dambrun',
                true,
            ],

            // ── Sarah Fontaine ───────────────────────────────────────────────
            [
                PersonFixture::SARAH,
                CharacteristicTypesFixture::GITHUB,
                'SarahFontaine',
                true,
            ],
            [
                PersonFixture::SARAH,
                CharacteristicTypesFixture::INSTAGRAM,
                'sarah.a11y',
                true,
            ],
            [
                PersonFixture::SARAH,
                CharacteristicTypesFixture::EMAIL,
                'sarah.fontaine@etu.univ-lyon1.fr',
                true,
            ],
            [
                PersonFixture::SARAH,
                CharacteristicTypesFixture::LINKEDIN,
                'sarah-fontaine-a11y',
                true,
            ],
            [
                PersonFixture::SARAH,
                CharacteristicTypesFixture::TWITTER,
                'SarahA11y',
                true,
            ],

            // ── Julian Rousseau ──────────────────────────────────────────────
            [
                PersonFixture::JULIAN,
                CharacteristicTypesFixture::GITHUB,
                'JulianRousseau',
                true,
            ],
            [
                PersonFixture::JULIAN,
                CharacteristicTypesFixture::EMAIL,
                'julian.rousseau@etu.univ-lyon1.fr',
                true,
            ],
            [
                PersonFixture::JULIAN,
                CharacteristicTypesFixture::PHONE,
                '+33 7 23 45 67 89',
                false,
            ],

            // ── Emma Girard ──────────────────────────────────────────────────
            [
                PersonFixture::EMMA,
                CharacteristicTypesFixture::GITHUB,
                'EmmaGirard',
                true,
            ],
            [
                PersonFixture::EMMA,
                CharacteristicTypesFixture::INSTAGRAM,
                'emma.ts',
                false,
            ],
            [
                PersonFixture::EMMA,
                CharacteristicTypesFixture::EMAIL,
                'emma.girard@etu.univ-lyon1.fr',
                true,
            ],
            [
                PersonFixture::EMMA,
                CharacteristicTypesFixture::LINKEDIN,
                'emma-girard-dev',
                true,
            ],
            [
                PersonFixture::EMMA,
                CharacteristicTypesFixture::DISCORD,
                'emma_vue#5678',
                false,
            ],

            // ── Romain Lefevre ───────────────────────────────────────────────
            [
                PersonFixture::ROMAIN,
                CharacteristicTypesFixture::GITHUB,
                'RomainLefevre-AI',
                true,
            ],
            [
                PersonFixture::ROMAIN,
                CharacteristicTypesFixture::TWITTER,
                'RomainAI',
                true,
            ],
            [
                PersonFixture::ROMAIN,
                CharacteristicTypesFixture::EMAIL,
                'romain.lefevre@etu.univ-lyon1.fr',
                true,
            ],
            [
                PersonFixture::ROMAIN,
                CharacteristicTypesFixture::LINKEDIN,
                'romain-lefevre-ai',
                true,
            ],

            // ── Clara Martin ─────────────────────────────────────────────────
            [
                PersonFixture::CLARA,
                CharacteristicTypesFixture::GITHUB,
                'ClaraMartin',
                true,
            ],
            [
                PersonFixture::CLARA,
                CharacteristicTypesFixture::INSTAGRAM,
                'clara.ecodev',
                true,
            ],
            [
                PersonFixture::CLARA,
                CharacteristicTypesFixture::EMAIL,
                'clara.martin@etu.univ-lyon1.fr',
                true,
            ],
            [
                PersonFixture::CLARA,
                CharacteristicTypesFixture::LINKEDIN,
                'clara-martin-ecodev',
                true,
            ],

            // ── Maxime Dubois ────────────────────────────────────────────────
            [
                PersonFixture::MAXIME,
                CharacteristicTypesFixture::GITHUB,
                'MaximeDubois',
                true,
            ],
            [
                PersonFixture::MAXIME,
                CharacteristicTypesFixture::TWITTER,
                'MaximeWeb3',
                true,
            ],
            [
                PersonFixture::MAXIME,
                CharacteristicTypesFixture::EMAIL,
                'maxime.dubois@etu.univ-lyon1.fr',
                true,
            ],
            [
                PersonFixture::MAXIME,
                CharacteristicTypesFixture::DISCORD,
                'maxime_chain#9999',
                false,
            ],

            // ── Zoé Lambert ──────────────────────────────────────────────────
            [
                PersonFixture::ZOE,
                CharacteristicTypesFixture::GITHUB,
                'ZoeLambert',
                true,
            ],
            [
                PersonFixture::ZOE,
                CharacteristicTypesFixture::INSTAGRAM,
                'zoe.css',
                true,
            ],
            [
                PersonFixture::ZOE,
                CharacteristicTypesFixture::EMAIL,
                'zoe.lambert@etu.univ-lyon1.fr',
                true,
            ],

            // ── Lucas Mercier ─────────────────────────────────────────────────
            [
                PersonFixture::LUCAS,
                CharacteristicTypesFixture::GITHUB,
                'LucasMercier',
                true,
            ],
            [
                PersonFixture::LUCAS,
                CharacteristicTypesFixture::EMAIL,
                'lucas.mercier@etu.univ-lyon1.fr',
                true,
            ],
            [
                PersonFixture::LUCAS,
                CharacteristicTypesFixture::DISCORD,
                'lucas_robo#2023',
                false,
            ],

            // ── Inès Perrin ───────────────────────────────────────────────────
            [
                PersonFixture::INES,
                CharacteristicTypesFixture::GITHUB,
                'InesPerrin',
                true,
            ],
            [
                PersonFixture::INES,
                CharacteristicTypesFixture::EMAIL,
                'ines.perrin@etu.univ-lyon1.fr',
                true,
            ],
            [
                PersonFixture::INES,
                CharacteristicTypesFixture::TWITTER,
                'InesOSS',
                true,
            ],
            [
                PersonFixture::INES,
                CharacteristicTypesFixture::LINKEDIN,
                'ines-perrin',
                true,
            ],

            // ── Théo Garnier ──────────────────────────────────────────────────
            [
                PersonFixture::THEO,
                CharacteristicTypesFixture::GITHUB,
                'TheoGarnier',
                true,
            ],
            [
                PersonFixture::THEO,
                CharacteristicTypesFixture::DISCORD,
                'theo_gamer#0420',
                true,
            ],
            [
                PersonFixture::THEO,
                CharacteristicTypesFixture::EMAIL,
                'theo.garnier@etu.univ-lyon1.fr',
                true,
            ],

            // ── Manon Richard ─────────────────────────────────────────────────
            [
                PersonFixture::MANON,
                CharacteristicTypesFixture::GITHUB,
                'ManonRichard',
                true,
            ],
            [
                PersonFixture::MANON,
                CharacteristicTypesFixture::LINKEDIN,
                'manon-richard-rgpd',
                true,
            ],
            [
                PersonFixture::MANON,
                CharacteristicTypesFixture::EMAIL,
                'manon.richard@etu.univ-lyon1.fr',
                true,
            ],
            [
                PersonFixture::MANON,
                CharacteristicTypesFixture::TWITTER,
                'ManonRGPD',
                false,
            ],
        ];

        foreach ($data as [$personRef, $typeRef, $value, $visible]) {
            $characteristic = new Characteristic()
                ->setPerson($this->getReference($personRef, Person::class))
                ->setType($this->getReference($typeRef, CharacteristicType::class))
                ->setValue($value)
                ->setVisible($visible);
            $manager->persist($characteristic);
        }

        $manager->flush();
    }

    /**
     * @return string[]
     */
    #[\Override]
    public function getDependencies(): array
    {
        return [
            CharacteristicTypesFixture::class,
            PersonFixture::class,
        ];
    }
}
