import { Link } from 'react-router';
import { PROMO_PALETTE } from '../../lib/colors';

const FOUNDERS = [
  {
    id: 7,
    firstName: 'Lilian',
    lastName: 'Baudry',
    image: '/images/founders/Lilian.jpg',
    color: PROMO_PALETTE[0],
  },
  {
    id: 52,
    firstName: 'Melvyn',
    lastName: 'Delpree',
    image: '/images/founders/Melvyn.png',
    color: PROMO_PALETTE[1],
  },
  {
    id: 6,
    firstName: 'Vincent',
    lastName: 'Chavot–Dambrun',
    image: '/images/founders/Vincent.jpg',
    color: PROMO_PALETTE[3],
  },
  {
    id: 1,
    firstName: 'Luka',
    lastName: 'Maret',
    image: '/images/founders/Luka.jpg',
    color: PROMO_PALETTE[4],
  },
] as const;

function FounderCard({ founder }: { founder: (typeof FOUNDERS)[number] }) {
  const { id, firstName, lastName, image, color } = founder;
  return (
    <Link
      to={`/person/${id}`}
      className="group flex flex-col overflow-hidden rounded-2xl border border-line bg-surface transition-all duration-150 hover:-translate-y-0.5 hover:border-ink hover:shadow-md"
    >
      <div className="relative h-48 w-full overflow-hidden" style={{ background: `${color}18` }}>
        <div
          className="pointer-events-none absolute inset-0"
          style={{ background: `linear-gradient(135deg, ${color}28 0%, transparent 60%)` }}
        />
        <img
          src={image}
          alt={`${firstName} ${lastName}`}
          className="h-full w-full object-cover object-top"
        />
      </div>
      <div className="p-4">
        <div className="text-[15px] font-semibold capitalize tracking-tight text-ink">
          {firstName.toLowerCase()} <span className="text-ink-2">{lastName.toLowerCase()}</span>
        </div>
        <div
          className="mt-1.5 text-[12px] font-medium transition-opacity group-hover:opacity-100"
          style={{ color, opacity: 0.7 }}
        >
          Voir le profil →
        </div>
      </div>
    </Link>
  );
}

export function AboutPage() {
  return (
    <div className="mx-auto max-w-4xl px-6 py-10">
      {/* Header */}
      <div className="mb-10 flex flex-col items-start gap-6 md:flex-row md:items-center">
        <img
          src="/images/icons/logo-blue.svg"
          alt="Logo Parraindex"
          className="h-20 w-20 shrink-0 opacity-90"
        />
        <div>
          <h1 className="mb-1 text-[32px] font-semibold tracking-tight text-ink">À propos</h1>
          <p className="text-[15px] text-ink-3">{"L'annuaire des parrainages de l'IUT Lyon 1"}</p>
        </div>
      </div>

      {/* Description */}
      <div className="mb-12 space-y-4 rounded-2xl border border-line bg-surface p-7 text-[15px] leading-relaxed text-ink-2">
        <p>
          {'Le Parraindex est un site web qui permet de trouver les'}{' '}
          <strong className="font-semibold text-ink">parrains</strong> et les{' '}
          <strong className="font-semibold text-ink">fillots</strong>{' '}
          {
            "de votre université. À l'origine, il a été créé pour l'IUT Informatique de l'Université Lyon 1, mais nous souhaitons l'étendre à terme à d'autres universités."
          }
        </p>
        <p>
          {'Le Parraindex a pour but de'}{' '}
          <strong className="font-semibold text-ink">recenser les liens de parrainage</strong>{' '}
          {
            'entre les étudiants, ce qui montre des liens forts. Le fait de voir son «arbre généalogique» permet de se rendre compte de la proximité des étudiants et de la qualité des liens de parrainage. Cela procure également une certaine satisfaction de visualiser son arbre généalogique. Faire partie d’une «famille» apporte un sentiment d’appartenance et facilite la sociabilisation.'
          }
        </p>
        <p>
          {'Le Parraindex est un'}{' '}
          <strong className="font-semibold text-ink">projet étudiant</strong>{' '}
          {
            "réalisé par 4 étudiants de l'IUT Informatique de l'Université Lyon 1. Ce site a été réalisé dans le cadre d'un projet d'étude et nous avons choisi de le rendre public afin de le partager avec le plus grand nombre."
          }
        </p>
      </div>

      {/* Fondateurs */}
      <div>
        <h2 className="mb-5 text-[20px] font-semibold tracking-tight text-ink">Les fondateurs</h2>
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
          {FOUNDERS.map((founder) => (
            <FounderCard key={founder.id} founder={founder} />
          ))}
        </div>
      </div>
    </div>
  );
}
