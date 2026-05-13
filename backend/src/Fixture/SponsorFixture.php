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
        $sponsors = [
            // ── Génération 2019 → 2020 ──────────────────────────────────────
            [
                PersonFixture::HENRI,
                PersonFixture::LILIAN,
                '2020-09-01',
                Type::CLASSIC,
                'Henri a repéré le potentiel de Lilian dès les journées d\'intégration.',
            ],
            [
                PersonFixture::CAMILLE,
                PersonFixture::MARINE,
                '2020-09-05',
                Type::CLASSIC,
                "Camille et Marine ont eu un coup de foudre amical lors d'un atelier dev web.",
            ],
            [
                PersonFixture::BAPTISTE,
                PersonFixture::THOMAS,
                '2020-09-10',
                Type::CLASSIC,
                'Baptiste a choisi Thomas pour sa curiosité débordante sur les réseaux.',
            ],
            [
                PersonFixture::CAMILLE,
                PersonFixture::PAULINE,
                '2020-09-08',
                Type::HEART,
                'Camille a voulu transmettre sa passion pour l\'UX à Pauline lors d\'une soirée de la promo.',
            ],

            // ── Génération 2020 → 2021 ──────────────────────────────────────
            [
                PersonFixture::LILIAN,
                PersonFixture::LUKA,
                '2021-09-03',
                Type::CLASSIC,
                'Lilian a immédiatement vu en Luka un développeur en devenir et n\'a pas hésité.',
            ],
            [
                PersonFixture::MARINE,
                PersonFixture::MELVYN,
                '2021-09-07',
                Type::CLASSIC,
                'Marine a choisi Melvyn pour sa passion de la sécurité, domaine qu\'elle affectionne.',
            ],
            [
                PersonFixture::THOMAS,
                PersonFixture::VINCENT,
                '2021-09-02',
                Type::CLASSIC,
                'Thomas a sélectionné Vincent pour son attachement à la qualité du code.',
            ],
            [
                PersonFixture::PAULINE,
                PersonFixture::SARAH,
                '2021-09-04',
                Type::HEART,
                'Pauline et Sarah se sont rencontrées lors d\'un hackathon et le courant est passé instantanément.',
            ],
            [
                PersonFixture::THOMAS,
                PersonFixture::JULIAN,
                '2021-09-06',
                Type::CLASSIC,
                'Thomas a pris Julian sous son aile pour partager sa maîtrise des réseaux.',
            ],

            // ── Génération 2021 → 2022 ──────────────────────────────────────
            [
                PersonFixture::LUKA,
                PersonFixture::EMMA,
                '2022-09-01',
                Type::CLASSIC,
                'Luka a choisi Emma pour son enthousiasme pour le développement web moderne.',
            ],
            [
                PersonFixture::MELVYN,
                PersonFixture::ROMAIN,
                '2022-09-03',
                Type::CLASSIC,
                'Melvyn a repéré les talents de Romain lors des tests de rentrée.',
            ],
            [
                PersonFixture::SARAH,
                PersonFixture::CLARA,
                '2022-09-05',
                Type::HEART,
                'Sarah et Clara ont sympathisé autour de leur intérêt commun pour l\'éco-conception.',
            ],
            [
                PersonFixture::JULIAN,
                PersonFixture::MAXIME,
                '2022-09-02',
                Type::CLASSIC,
                'Julian a orienté Maxime vers les technologies décentralisées après une discussion passionnante.',
            ],
            [
                PersonFixture::VINCENT,
                PersonFixture::MAXIME,
                '2022-10-15',
                Type::HEART,
                'Vincent a décidé d\'adopter Maxime en parrainage de coeur après plusieurs sessions de code ensemble.',
            ],

            // ── Liens sautant une promotion ─────────────────────────────────
            [
                PersonFixture::HENRI,
                PersonFixture::LUKA,
                '2021-09-15',
                Type::HEART,
                'Henri a suivi Luka de loin depuis son entrée à l\'IUT et a voulu formaliser ce lien de parrainage de cœur, au-delà de la chaîne classique.',
            ],
            [
                PersonFixture::CAMILLE,
                PersonFixture::VINCENT,
                '2021-10-01',
                Type::CLASSIC,
                'Camille a rencontré Vincent lors d\'un atelier clean code et a voulu transmettre directement sa rigueur de développeuse, sans attendre.',
            ],
            [
                PersonFixture::THOMAS,
                PersonFixture::EMMA,
                '2022-09-20',
                Type::CLASSIC,
                'Thomas a repéré Emma lors d\'une session de mentorat inter-promos et a souhaité l\'accompagner malgré l\'écart de promotion.',
            ],

            // ── Génération 2022 → 2023 ──────────────────────────────────────
            [
                PersonFixture::EMMA,
                PersonFixture::ZOE,
                '2023-09-01',
                Type::CLASSIC,
                'Emma a immédiatement su qu\'elle voulait être la marraine de Zoé en voyant son portfolio CSS.',
            ],
            [
                PersonFixture::ROMAIN,
                PersonFixture::LUCAS,
                '2023-09-04',
                Type::CLASSIC,
                'Romain a choisi Lucas pour ses projets de robotique impressionnants.',
            ],
            [
                PersonFixture::CLARA,
                PersonFixture::INES,
                '2023-09-02',
                Type::CLASSIC,
                'Clara a recruté Inès pour ses contributions open source déjà visibles sur GitHub.',
            ],
            [
                PersonFixture::MAXIME,
                PersonFixture::THEO,
                '2023-09-06',
                Type::CLASSIC,
                'Maxime a vu en Théo un futur développeur Web3 après une discussion sur les jeux décentralisés.',
            ],
            [
                PersonFixture::EMMA,
                PersonFixture::MANON,
                '2023-09-08',
                Type::HEART,
                'Emma a pris Manon en parrainage de cœur pour son approche unique mêlant droit et technique.',
            ],
        ];

        foreach ($sponsors as [$godFatherRef, $godChildRef, $date, $type, $description]) {
            /** @var Person $godFather */
            $godFather = $this->getReference($godFatherRef, Person::class);
            /** @var Person $godChild */
            $godChild = $this->getReference($godChildRef, Person::class);

            $sponsor = new Sponsor()
                ->setGodFather($godFather)
                ->setGodChild($godChild)
                ->setDate(new \DateTime($date))
                ->setType($type)
                ->setDescription($description)
                ->setCreatedAt(new \DateTime($date));
            $manager->persist($sponsor);
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
