<?php

declare(strict_types=1);

namespace App\Fixture;

use App\Entity\Person\Person;
use App\Repository\PersonRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PersonFixture extends Fixture
{
    // Promotion 2019 (parrains des parrains)
    public const string HENRI = 'person_henri';

    public const string CAMILLE = 'person_camille';

    public const string BAPTISTE = 'person_baptiste';

    // Promotion 2020 (parrains de la promo 2021)
    public const string LILIAN = 'person_lilian';

    public const string MARINE = 'person_marine';

    public const string THOMAS = 'person_thomas';

    public const string PAULINE = 'person_pauline';

    // Promotion 2021 (parrains de la promo 2022)
    public const string LUKA = 'person_luka';

    public const string MELVYN = 'person_melvyn';

    public const string VINCENT = 'person_vincent';

    public const string SARAH = 'person_sarah';

    public const string JULIAN = 'person_julian';

    // Promotion 2022 (parrains de la promo 2023)
    public const string EMMA = 'person_emma';

    public const string ROMAIN = 'person_romain';

    public const string CLARA = 'person_clara';

    public const string MAXIME = 'person_maxime';

    // Promotion 2023 (filleuls récents)
    public const string ZOE = 'person_zoe';

    public const string LUCAS = 'person_lucas';

    public const string INES = 'person_ines';

    public const string THEO = 'person_theo';

    public const string MANON = 'person_manon';

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        // ── Promotion 2019 ──────────────────────────────────────────────────
        $henri = new Person()
            ->setFirstName('Henri')
            ->setLastName('Durand')
            ->setDescription('Étudiant en BUT Informatique, passionné de sécurité informatique.')
            ->setBiography('J\'ai rejoint l\'IUT en 2019 et j\'ai adoré chaque instant. La cybersécurité est ma passion et j\'essaie de transmettre cette curiosité à mes filleuls.')
            ->setColor('#2E4057')
            ->setStartYear(2019)
            ->setBirthdate(new \DateTimeImmutable('2001-03-14'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2019-09-01'));
        $manager->persist($henri);
        $this->addReference(self::HENRI, $henri);

        $camille = new Person()
            ->setFirstName('Camille')
            ->setLastName('Leclerc')
            ->setDescription('Développeuse web passionnée, spécialisée en React et Node.js.')
            ->setBiography('Après mon BUT, j\'ai intégré une startup lyonnaise en tant que développeuse fullstack. Je reste proche de l\'IUT pour transmettre mon expérience.')
            ->setColor('#A8DADC')
            ->setStartYear(2019)
            ->setBirthdate(new \DateTimeImmutable('2001-07-22'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2019-09-01'));
        $manager->persist($camille);
        $this->addReference(self::CAMILLE, $camille);

        $baptiste = new Person()
            ->setFirstName('Baptiste')
            ->setLastName('Moreau')
            ->setDescription('Fan de jeux vidéo et développeur de jeux indépendants.')
            ->setBiography('La programmation de jeux m\'a amené à l\'informatique. Aujourd\'hui je travaille chez un studio de jeux à Paris tout en gardant des liens avec l\'IUT.')
            ->setColor('#E63946')
            ->setStartYear(2019)
            ->setBirthdate(new \DateTimeImmutable('2000-11-05'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2019-09-01'));
        $manager->persist($baptiste);
        $this->addReference(self::BAPTISTE, $baptiste);

        // ── Promotion 2020 ──────────────────────────────────────────────────
        $lilian = new Person()
            ->setFirstName('Lilian')
            ->setLastName('Baudry')
            ->setDescription('Développeur backend, amoureux de Python et des APIs REST.')
            ->setBiography('J\'ai découvert la programmation au lycée et l\'IUT a concrétisé ma passion. Filleul de Henri, je suis maintenant parrain à mon tour et c\'est une expérience incroyable.')
            ->setColor('#1D3557')
            ->setStartYear(2020)
            ->setBirthdate(new \DateTimeImmutable('2002-01-18'))
            ->setPicture('Lilian.jpg')
            ->setCreatedAt(new \DateTime('2020-09-01'));
        $manager->persist($lilian);
        $this->addReference(self::LILIAN, $lilian);

        $marine = new Person()
            ->setFirstName('Marine')
            ->setLastName('Petit')
            ->setDescription('Passionnée de data science et de machine learning.')
            ->setBiography('Les données me fascinent depuis toujours. Filleule de Camille, j\'explore maintenant les joies de la data science et je partage cette passion avec mes filleuls.')
            ->setColor('#F4A261')
            ->setStartYear(2020)
            ->setBirthdate(new \DateTimeImmutable('2002-04-30'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2020-09-01'));
        $manager->persist($marine);
        $this->addReference(self::MARINE, $marine);

        $thomas = new Person()
            ->setFirstName('Thomas')
            ->setLastName('Bernard')
            ->setDescription('Administrateur systèmes et réseaux en devenir.')
            ->setBiography('Je suis venu à l\'IUT avec l\'envie de comprendre comment Internet fonctionne. Mon parrain Baptiste m\'a mis sur la voie du DevOps et je ne le regrette pas.')
            ->setColor('#2A9D8F')
            ->setStartYear(2020)
            ->setBirthdate(new \DateTimeImmutable('2001-08-15'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2020-09-01'));
        $manager->persist($thomas);
        $this->addReference(self::THOMAS, $thomas);

        $pauline = new Person()
            ->setFirstName('Pauline')
            ->setLastName('Simon')
            ->setDescription('Développeuse mobile iOS/Android, fan d\'UX design.')
            ->setBiography('L\'ergonomie et l\'expérience utilisateur me passionnent autant que le code. Je conçois des applications mobiles qui plaisent vraiment aux utilisateurs.')
            ->setColor('#E9C46A')
            ->setStartYear(2020)
            ->setBirthdate(new \DateTimeImmutable('2002-02-28'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2020-09-01'));
        $manager->persist($pauline);
        $this->addReference(self::PAULINE, $pauline);

        // ── Promotion 2021 ──────────────────────────────────────────────────
        $luka = new Person()
            ->setFirstName('Luka')
            ->setLastName('Maret')
            ->setDescription('Développeur fullstack, créateur de Parraindex.')
            ->setBiography('Je suis arrivé à l\'IUT en 2021 avec l\'envie de créer des outils utiles.')
            ->setColor('#0077B6')
            ->setStartYear(2021)
            ->setBirthdate(new \DateTimeImmutable('2003-05-12'))
            ->setPicture('Luka.jpg')
            ->setCreatedAt(new \DateTime('2021-09-01'));
        $manager->persist($luka);
        $this->addReference(self::LUKA, $luka);

        $melvyn = new Person()
            ->setFirstName('Melvyn')
            ->setLastName('Delpree')
            ->setDescription('Passionné de cybersécurité et de CTF.')
            ->setBiography('Les CTF (Capture The Flag) sont ma drogue. Filleul de Marine, j\'ai découvert la sécurité offensive et je participe à des compétitions nationales.')
            ->setColor('#A52A2A')
            ->setStartYear(2021)
            ->setBirthdate(new \DateTimeImmutable('2003-09-03'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2021-09-01'));
        $manager->persist($melvyn);
        $this->addReference(self::MELVYN, $melvyn);

        $vincent = new Person()
            ->setFirstName('Vincent')
            ->setLastName('Chavot--Dambrun')
            ->setDescription('Développeur backend, féru d\'architecture logicielle et de clean code.')
            ->setBiography('La qualité du code est une obsession saine. Filleul de Thomas, j\'ai appris à construire des systèmes robustes et maintenables. SOLID, DDD, ça me parle !')
            ->setColor('#C1121F')
            ->setStartYear(2021)
            ->setBirthdate(new \DateTimeImmutable('2002-12-20'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2021-09-01'));
        $manager->persist($vincent);
        $this->addReference(self::VINCENT, $vincent);

        $sarah = new Person()
            ->setFirstName('Sarah')
            ->setLastName('Fontaine')
            ->setDescription('Développeuse front-end, experte en accessibilité web.')
            ->setBiography('Rendre le web accessible à tous est une cause qui me tient à cœur. Ma marraine Pauline m\'a transmis cette passion pour l\'UX et je la pousse encore plus loin.')
            ->setColor('#8338EC')
            ->setStartYear(2021)
            ->setBirthdate(new \DateTimeImmutable('2003-07-08'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2021-09-01'));
        $manager->persist($sarah);
        $this->addReference(self::SARAH, $sarah);

        $julian = new Person()
            ->setFirstName('Julian')
            ->setLastName('Rousseau')
            ->setDescription('Étudiant en alternance chez Orange, spécialisé en réseaux.')
            ->setBiography('L\'alternance m\'a permis de combiner théorie et pratique. Les réseaux et la téléphonie sont mes domaines de prédilection, hérités de mon parrain Thomas.')
            ->setColor('#FB5607')
            ->setStartYear(2021)
            ->setBirthdate(new \DateTimeImmutable('2003-03-25'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2021-09-01'));
        $manager->persist($julian);
        $this->addReference(self::JULIAN, $julian);

        // ── Promotion 2022 ──────────────────────────────────────────────────
        $emma = new Person()
            ->setFirstName('Emma')
            ->setLastName('Girard')
            ->setDescription('Développeuse web, passionnée de TypeScript et de Vue.js.')
            ->setBiography('Filleule de Luka, j\'ai rapidement adopté les bonnes pratiques de développement.')
            ->setColor('#3A86FF')
            ->setStartYear(2022)
            ->setBirthdate(new \DateTimeImmutable('2004-01-14'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2022-09-01'));
        $manager->persist($emma);
        $this->addReference(self::EMMA, $emma);

        $romain = new Person()
            ->setFirstName('Romain')
            ->setLastName('Lefevre')
            ->setDescription('Féru d\'intelligence artificielle et de Python.')
            ->setBiography('Machine learning, réseaux de neurones, LLMs... Mon parrain Melvyn m\'a initié à la programmation avancée et j\'ai rapidement bifurqué vers l\'IA.')
            ->setColor('#06D6A0')
            ->setStartYear(2022)
            ->setBirthdate(new \DateTimeImmutable('2004-06-22'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2022-09-01'));
        $manager->persist($romain);
        $this->addReference(self::ROMAIN, $romain);

        $clara = new Person()
            ->setFirstName('Clara')
            ->setLastName('Martin')
            ->setDescription('Passionnée de développement durable et d\'éco-conception web.')
            ->setBiography('Le numérique responsable n\'est pas un oxymore ! Ma marraine Sarah m\'a appris l\'importance de l\'accessibilité, j\'y ai ajouté l\'impact environnemental.')
            ->setColor('#118AB2')
            ->setStartYear(2022)
            ->setBirthdate(new \DateTimeImmutable('2004-09-11'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2022-09-01'));
        $manager->persist($clara);
        $this->addReference(self::CLARA, $clara);

        $maxime = new Person()
            ->setFirstName('Maxime')
            ->setLastName('Dubois')
            ->setDescription('Développeur blockchain et smart contracts.')
            ->setBiography('La décentralisation et le Web3 m\'ont fasciné dès le lycée. Mon parrain Julian m\'a aidé à structurer mon apprentissage et je travaille maintenant sur des projets DeFi.')
            ->setColor('#FFD166')
            ->setStartYear(2022)
            ->setBirthdate(new \DateTimeImmutable('2004-04-01'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2022-09-01'));
        $manager->persist($maxime);
        $this->addReference(self::MAXIME, $maxime);

        // ── Promotion 2023 ──────────────────────────────────────────────────
        $zoe = new Person()
            ->setFirstName('Zoé')
            ->setLastName('Lambert')
            ->setDescription('Développeuse junior, amoureuse du CSS et des animations.')
            ->setBiography('Le CSS est un vrai langage de programmation, je le dis et je l\'assume ! Ma marraine Emma m\'a convaincue de me lancer dans le développement web et je ne regrette pas.')
            ->setColor('#EF476F')
            ->setStartYear(2023)
            ->setBirthdate(new \DateTimeImmutable('2005-02-14'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2023-09-01'));
        $manager->persist($zoe);
        $this->addReference(self::ZOE, $zoe);

        $lucas = new Person()
            ->setFirstName('Lucas')
            ->setLastName('Mercier')
            ->setDescription('Étudiant en BUT, passionné de robotique et de systèmes embarqués.')
            ->setBiography('J\'ai construit mon premier robot à 12 ans avec des Lego Mindstorms. Mon parrain Romain m\'a introduit à l\'IA embarquée et c\'est une révélation.')
            ->setColor('#073B4C')
            ->setStartYear(2023)
            ->setBirthdate(new \DateTimeImmutable('2005-08-30'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2023-09-01'));
        $manager->persist($lucas);
        $this->addReference(self::LUCAS, $lucas);

        $ines = new Person()
            ->setFirstName('Inès')
            ->setLastName('Perrin')
            ->setDescription('Curieuse de tout, particulièrement de l\'open source et de Linux.')
            ->setBiography('Je contribue à des projets open source depuis mes 16 ans. Ma marraine Clara m\'a guidée dans les pratiques collaboratives.')
            ->setColor('#264653')
            ->setStartYear(2023)
            ->setBirthdate(new \DateTimeImmutable('2005-05-19'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2023-09-01'));
        $manager->persist($ines);
        $this->addReference(self::INES, $ines);

        $theo = new Person()
            ->setFirstName('Théo')
            ->setLastName('Garnier')
            ->setDescription('Fan de gaming et développeur de mods pour jeux vidéo.')
            ->setBiography('J\'ai commencé à coder pour modder Minecraft. Mon parrain Maxime m\'a montré que mes compétences pouvaient s\'appliquer à des projets bien plus ambitieux.')
            ->setColor('#E76F51')
            ->setStartYear(2023)
            ->setBirthdate(new \DateTimeImmutable('2005-11-07'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2023-09-01'));
        $manager->persist($theo);
        $this->addReference(self::THEO, $theo);

        $manon = new Person()
            ->setFirstName('Manon')
            ->setLastName('Richard')
            ->setDescription('Étudiante en BUT, intéressée par le droit du numérique et la RGPD.')
            ->setBiography('L\'intersection entre le droit et le numérique est un domaine passionnant et trop peu exploré. Je m\'oriente vers la conformité et la protection des données personnelles.')
            ->setColor('#457B9D')
            ->setStartYear(2023)
            ->setBirthdate(new \DateTimeImmutable('2005-03-27'))
            ->setPicture(PersonRepository::DEFAULT_PICTURE)
            ->setCreatedAt(new \DateTime('2023-09-01'));
        $manager->persist($manon);
        $this->addReference(self::MANON, $manon);

        $manager->flush();
    }
}
