import { Link } from 'react-router';

interface FeatureCard {
  title: string;
  description: string;
  to: string;
  iconPath: string;
}

const CARDS: FeatureCard[] = [
  {
    title: 'Annuaire',
    description: 'Parcourez tous les étudiants en grille, liste ou arbre de parrainage.',
    to: '/tree',
    iconPath: 'M3 4h14M3 9h14M3 14h14',
  },
  {
    title: 'Profil',
    description: 'Découvrez les parrains, fillots, biographie et liens de chaque étudiant.',
    to: '/tree',
    iconPath: 'M10 10a3 3 0 100-6 3 3 0 000 6zM2 17a8 8 0 0116 0',
  },
  {
    title: 'À propos',
    description: 'En savoir plus sur le projet Parraindex et comment y contribuer.',
    to: '/about',
    iconPath: 'M5 10h10M11 6l4 4-4 4',
  },
];

export function FeatureCards() {
  return (
    <div
      className="grid gap-4"
      style={{ gridTemplateColumns: 'repeat(auto-fit, minmax(260px, 1fr))' }}
    >
      {CARDS.map((card) => (
        <Link
          key={card.title}
          to={card.to}
          className="group block rounded-xl border border-line bg-surface p-6 text-left transition-all duration-150 hover:-translate-y-0.5 hover:border-ink"
        >
          <div className="mb-3.5 flex h-9 w-9 items-center justify-center rounded-[9px] bg-bg">
            <svg
              width={18}
              height={18}
              viewBox="0 0 20 20"
              fill="none"
              stroke="currentColor"
              strokeWidth={1.5}
              strokeLinecap="round"
              strokeLinejoin="round"
              className="text-ink"
            >
              <path d={card.iconPath} />
            </svg>
          </div>
          <div className="mb-1.5 text-[15px] font-semibold tracking-tight text-ink">
            {card.title}
          </div>
          <div className="text-body-sm leading-relaxed text-ink-3">{card.description}</div>
        </Link>
      ))}
    </div>
  );
}
