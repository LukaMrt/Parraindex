import { useEffect } from 'react';
import { Link } from 'react-router';
import { SPONSOR_TYPE_ICONS, SPONSOR_TYPE_LABELS } from '../../lib/sponsorTypes';
import { promoColor } from '../../lib/colors';
import type { Sponsor } from '../../types/sponsor';

interface SponsorInfoCardProps {
  sponsor: Sponsor;
  godFatherStartYear?: number | undefined;
  onClose: () => void;
}

/** "2021-09-15" → "septembre 2021", sans passer par Date (évite le décalage UTC → local) */
function formatSponsorDate(raw: string | null): string | null {
  if (!raw) return null;
  const [y, m] = raw.split('-').map(Number);
  if (!y) return null;
  if (!m) return String(y);
  return new Date(y, m - 1, 1).toLocaleDateString('fr-FR', { year: 'numeric', month: 'long' });
}

/**
 * Détail d'un lien de parrainage, affiché en bas à droite du canvas
 * (les contrôles de zoom occupent le haut à droite). Échap ferme la carte.
 */
export function SponsorInfoCard({ sponsor, godFatherStartYear, onClose }: SponsorInfoCardProps) {
  const date = formatSponsorDate(sponsor.date);
  const color = promoColor(godFatherStartYear ?? Number(sponsor.date?.slice(0, 4) ?? 2019));

  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape') onClose();
    };
    window.addEventListener('keydown', onKey);
    return () => {
      window.removeEventListener('keydown', onKey);
    };
  }, [onClose]);

  return (
    <div
      role="dialog"
      aria-label={`Parrainage ${SPONSOR_TYPE_LABELS[sponsor.type]}`}
      data-pz-ignore
      className="absolute bottom-14 right-3 z-20 w-[min(16rem,calc(100%-1.5rem))] overflow-hidden rounded-xl border border-line bg-surface shadow-lg sm:bottom-3"
    >
      <div
        className="flex items-center justify-between px-3 py-2"
        style={{ borderBottom: `1px solid ${color}30`, background: `${color}10` }}
      >
        <div className="flex items-center gap-1.5 text-[12px] font-semibold" style={{ color }}>
          <span className="text-base" aria-hidden>
            {SPONSOR_TYPE_ICONS[sponsor.type]}
          </span>
          <span>Parrainage {SPONSOR_TYPE_LABELS[sponsor.type]}</span>
        </div>
        <button
          onClick={onClose}
          aria-label="Fermer"
          className="flex h-6 w-6 cursor-pointer items-center justify-center rounded-full text-[11px] text-ink-3 transition-colors hover:bg-line hover:text-ink"
        >
          ✕
        </button>
      </div>

      <div className="px-3 py-2.5">
        <div className="flex flex-wrap items-center gap-1.5 text-[12.5px]">
          <Link
            to={`/person/${String(sponsor.godFatherId)}`}
            className="font-medium text-ink underline-offset-2 hover:underline"
          >
            {sponsor.godFatherName}
          </Link>
          <span className="text-ink-3" aria-label="parrain de">
            →
          </span>
          <Link
            to={`/person/${String(sponsor.godChildId)}`}
            className="font-medium text-ink underline-offset-2 hover:underline"
          >
            {sponsor.godChildName}
          </Link>
        </div>
        {date && <p className="mt-0.5 text-[11px] text-ink-3">{date}</p>}
      </div>

      {sponsor.description && (
        <div className="border-t border-line px-3 py-2.5">
          <p className="text-[12px] leading-relaxed text-ink-2">{sponsor.description}</p>
        </div>
      )}
    </div>
  );
}
