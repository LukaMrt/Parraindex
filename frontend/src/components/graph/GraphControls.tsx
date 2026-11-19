import type { ReactNode } from 'react';
import { cn } from '../../lib/cn';

const ZOOM_STEP = 1.2;

interface ZoomControlsProps {
  zoomBy: (factor: number) => void;
  onFit: () => void;
  className?: string;
}

/** Boutons +, −, « ajuster » en haut à droite d'un canvas de graphe */
export function ZoomControls({ zoomBy, onFit, className }: ZoomControlsProps) {
  const buttons: { label: string; symbol: string; action: () => void }[] = [
    {
      label: 'Zoomer',
      symbol: '+',
      action: () => {
        zoomBy(ZOOM_STEP);
      },
    },
    {
      label: 'Dézoomer',
      symbol: '−',
      action: () => {
        zoomBy(1 / ZOOM_STEP);
      },
    },
    { label: 'Ajuster à la vue', symbol: '⤢', action: onFit },
  ];
  return (
    <div className={cn('absolute right-3 top-3 z-10 flex flex-col gap-1.5', className)}>
      {buttons.map(({ label, symbol, action }) => (
        <button
          key={label}
          type="button"
          aria-label={label}
          title={label}
          onClick={action}
          className="flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg border border-line bg-surface text-sm font-semibold text-ink-2 transition-colors hover:border-ink hover:text-ink"
        >
          {symbol}
        </button>
      ))}
    </div>
  );
}

/** Indication affichée quand on fait défiler la page au-dessus d'un canvas (mode 'modifier') */
export function WheelHint({ visible }: { visible: boolean }) {
  const isMac = typeof navigator !== 'undefined' && /Mac|iP(hone|ad)/.test(navigator.platform);
  return (
    <div
      aria-hidden
      className={cn(
        'pointer-events-none absolute inset-0 z-30 flex items-center justify-center bg-ink/25 transition-opacity duration-200',
        visible ? 'opacity-100' : 'opacity-0',
      )}
    >
      <span className="rounded-lg bg-surface px-3 py-1.5 text-[12.5px] font-medium text-ink shadow">
        {isMac ? '⌘' : 'Ctrl'} + molette ou pincer pour zoomer
      </span>
    </div>
  );
}

/** Barre d'état en bas à gauche (compteurs, zoom, options) */
export function GraphStatusBar({ children }: { children: ReactNode }) {
  return (
    <div className="absolute bottom-3 left-3 z-10 flex max-w-[calc(100%-1.5rem)] flex-wrap items-center gap-x-3 gap-y-1 rounded-lg border border-line bg-surface px-3 py-1.5 text-[11.5px] text-ink-3">
      {children}
    </div>
  );
}

/** Triangle de direction centré en (mx, my) orienté selon le vecteur unitaire (ux, uy) */
export function arrowPoints(mx: number, my: number, ux: number, uy: number, s = 5): string {
  return [
    `${String(mx + ux * s)},${String(my + uy * s)}`,
    `${String(mx - ux * s - uy * s * 0.6)},${String(my - uy * s + ux * s * 0.6)}`,
    `${String(mx - ux * s + uy * s * 0.6)},${String(my - uy * s - ux * s * 0.6)}`,
  ].join(' ');
}
