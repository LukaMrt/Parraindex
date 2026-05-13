import { useState } from 'react';
import { Link } from 'react-router';
import { Skeleton } from '../../components/ui';
import type { PromoGroup } from './types';

const SKELETON_COUNT = 5;

interface PromoStripProps {
  promoGroups: PromoGroup[];
  loading: boolean;
}

function PromoCard({ year, color, count }: PromoGroup) {
  const [hovered, setHovered] = useState(false);

  return (
    <Link
      to={`/tree?year=${year}`}
      className="block rounded-xl border bg-surface p-3.5 text-left transition-colors duration-150"
      style={{ borderColor: hovered ? color : 'var(--color-line)' }}
      onMouseEnter={() => {
        setHovered(true);
      }}
      onMouseLeave={() => {
        setHovered(false);
      }}
    >
      <div className="mb-2.5 h-2 w-2 rounded-full" style={{ background: color }} />
      <div className="text-[13px] font-semibold text-ink">{year}</div>
      <div className="mt-0.5 text-[11.5px] text-ink-3">
        {count} étudiant{count > 1 ? 's' : ''}
      </div>
    </Link>
  );
}

function PromoCardSkeleton() {
  return (
    <div className="rounded-xl border border-line bg-surface p-3.5">
      <Skeleton className="mb-2.5 h-2 w-2 rounded-full" />
      <Skeleton className="mb-[5px] h-[19px] w-10" />
      <Skeleton className="h-[17px] w-16" />
    </div>
  );
}

export function PromoStrip({ promoGroups, loading }: PromoStripProps) {
  const cols = loading ? SKELETON_COUNT : promoGroups.length;

  return (
    <div className="mt-16">
      <div className="mb-4 flex items-baseline justify-between">
        <h2 className="text-[18px] font-semibold tracking-tight text-ink">Promotions actives</h2>
        <Link to="/tree" className="text-[13px] text-ink-3 transition-colors hover:text-ink">
          Voir tout →
        </Link>
      </div>
      <div className="grid gap-2.5" style={{ gridTemplateColumns: `repeat(${cols}, 1fr)` }}>
        {loading
          ? Array.from({ length: SKELETON_COUNT }, (_, i) => <PromoCardSkeleton key={i} />)
          : promoGroups.map((promo) => <PromoCard key={promo.year} {...promo} />)}
      </div>
    </div>
  );
}
