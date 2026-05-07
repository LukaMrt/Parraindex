import { Link } from 'react-router';
import { cn } from '../lib/cn';
import { FeatureCards } from './home/FeatureCards';
import { PromoStrip } from './home/PromoStrip';
import { useHomeStats } from './home/useHomeStats';

export function HomePage() {
  const { totalPersons, totalPromos, promoGroups, loading } = useHomeStats();

  return (
    <div className="relative flex min-h-[calc(100vh-var(--header-height))] items-center overflow-hidden bg-bg px-6 py-10">
      {/* Logo décoratif flou en arrière-plan */}
      <img
        src="/images/icons/logo-blue.svg"
        alt=""
        aria-hidden="true"
        className="pointer-events-none absolute -left-32 -top-32 w-[680px] select-none opacity-[0.07] blur-[1px]"
      />
      <div className="mx-auto w-full max-w-[1100px]">
        {/* Hero */}
        <div className="mb-16 text-center">
          {/* Stats pill */}
          <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-line bg-surface px-3 py-1.5 text-[12.5px] text-ink-3">
            <span className="h-1.5 w-1.5 rounded-full bg-success" />
            {loading ? (
              <span>Chargement…</span>
            ) : (
              <span>
                {totalPersons} étudiants · {totalPromos} promotions
              </span>
            )}
          </div>

          {/* Title */}
          <h1
            className="mb-5 font-semibold leading-[1.05] tracking-[-0.03em] text-ink"
            style={{ fontSize: 'clamp(40px, 6vw, 68px)' }}
          >
            {"L'annuaire des parrains"}
            <br />
            <span className="text-ink-3">{"de l'IUT Lyon 1"}</span>
          </h1>

          <p className="mx-auto mb-8 max-w-[540px] text-[17px] leading-relaxed text-ink-2">
            Visualisez les liens de parrainage entre étudiants, retrouvez votre famille, et explorez
            les promotions au fil des années.
          </p>

          {/* CTAs */}
          <div className="flex flex-wrap justify-center gap-3">
            <Link
              to="/tree"
              className={cn(
                'inline-flex h-[46px] items-center rounded-[10px] px-5 text-[14.5px] font-medium',
                'bg-ink text-white transition-opacity hover:opacity-90',
              )}
            >
              Explorer l&apos;annuaire →
            </Link>
            <Link
              to="/about"
              className={cn(
                'inline-flex h-[46px] items-center rounded-[10px] px-5 text-[14.5px] font-medium',
                'border border-line bg-surface text-ink transition-colors hover:border-ink',
              )}
            >
              En savoir plus
            </Link>
          </div>
        </div>

        {/* Feature cards */}
        <FeatureCards />

        {/* Promos strip */}
        <PromoStrip promoGroups={promoGroups} loading={loading} />
      </div>
    </div>
  );
}
