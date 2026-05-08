import { useQuery } from '@tanstack/react-query';
import { SPONSOR_TYPE_ICONS, SPONSOR_TYPE_LABELS } from '../../lib/sponsorTypes';
import { sponsorQueries } from '../../lib/queries';
import { promoColor } from '../../lib/colors';
import type { SponsorSummary } from '../../types/sponsor';

interface SponsorInfoCardProps {
  summary: SponsorSummary;
  godFatherStartYear?: number;
  onClose: () => void;
  position?: 'bottom-right' | 'top-right';
}

export function SponsorInfoCard({
  summary,
  godFatherStartYear,
  onClose,
  position = 'bottom-right',
}: SponsorInfoCardProps) {
  const { data: sponsor, isLoading: loading } = useQuery(sponsorQueries.detail(summary.id));

  const resolved = sponsor ?? summary;
  const typeIcon = SPONSOR_TYPE_ICONS[resolved.type];
  const typeLabel = SPONSOR_TYPE_LABELS[resolved.type];
  const rawDate = resolved.date;
  const date = rawDate
    ? new Date(rawDate).toLocaleDateString('fr-FR', { year: 'numeric', month: 'long' })
    : null;
  const fallbackYear = rawDate ? new Date(rawDate).getFullYear() || 2020 : 2020;
  const color = promoColor(godFatherStartYear ?? fallbackYear);

  return (
    <div
      className={`absolute right-3 z-20 w-64 overflow-hidden rounded-xl border border-line bg-surface shadow-lg ${position === 'top-right' ? 'top-3' : 'bottom-3'}`}
      onMouseDown={(e) => {
        e.stopPropagation();
      }}
    >
      {/* Header */}
      <div
        className="flex items-center justify-between px-3 py-2"
        style={{ borderBottom: `1px solid ${color}30`, background: `${color}10` }}
      >
        <div className="flex items-center gap-1.5 text-[12px] font-semibold" style={{ color }}>
          {loading && resolved.type === 'UNKNOWN' ? (
            <div className="h-3 w-32 animate-pulse rounded bg-line" />
          ) : (
            <>
              <span className="text-base">{typeIcon}</span>
              <span>Parrainage {typeLabel}</span>
            </>
          )}
        </div>
        <button
          onClick={onClose}
          className="flex h-5 w-5 cursor-pointer items-center justify-center rounded-full text-[11px] text-ink-3 transition-colors hover:bg-line hover:text-ink"
        >
          ✕
        </button>
      </div>

      {/* Personnes */}
      <div className="px-3 py-2.5">
        <div className="flex items-center gap-1.5 text-[12.5px]">
          <span className="font-medium text-ink">{summary.godFatherName}</span>
          <span className="text-ink-3">→</span>
          <span className="font-medium text-ink">{summary.godChildName}</span>
        </div>
        {date && <p className="mt-0.5 text-[11px] text-ink-3">{date}</p>}
      </div>

      {/* Description */}
      {loading ? (
        <div className="border-t border-line px-3 py-2.5">
          <div className="h-3 w-3/4 animate-pulse rounded bg-line" />
          <div className="mt-1.5 h-3 w-1/2 animate-pulse rounded bg-line" />
        </div>
      ) : sponsor?.description ? (
        <div className="border-t border-line px-3 py-2.5">
          <p className="text-[12px] leading-relaxed text-ink-2">{sponsor.description}</p>
        </div>
      ) : null}
    </div>
  );
}
