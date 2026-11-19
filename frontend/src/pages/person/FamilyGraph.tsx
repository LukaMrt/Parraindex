import { useEffect, useMemo, useState } from 'react';
import { usePersonNavigation } from '../../hooks/usePersonNavigation';
import { transformStyle, usePanZoom } from '../../hooks/usePanZoom';
import { Skeleton } from '../../components/ui';
import { PersonGraphNode } from '../../components/graph/PersonGraphNode';
import { SponsorInfoCard } from '../../components/graph/SponsorInfoCard';
import {
  GraphStatusBar,
  WheelHint,
  ZoomControls,
  arrowPoints,
} from '../../components/graph/GraphControls';
import { promoColor } from '../../lib/colors';
import type { Person } from '../../types/person';
import type { Sponsor } from '../../types/sponsor';
import { NODE_D, NODE_D_SELF, ROW_H, shortestPath } from './familyGraphLayout';
import { useFamilyGraph } from './useFamilyGraph';

// ── Sub-components ────────────────────────────────────────────────────────────

function DepthControl({
  label,
  hint,
  value,
  canShrink,
  canExpand,
  loading,
  onMinus,
  onPlus,
}: {
  label: string;
  hint: string;
  value: number;
  canShrink: boolean;
  canExpand: boolean;
  loading: boolean;
  onMinus: () => void;
  onPlus: () => void;
}) {
  return (
    <div
      className="flex items-center gap-0.5 rounded-full border border-line bg-surface px-1.5 py-0.5"
      title={hint}
    >
      <span className="px-1 text-[10.5px] text-ink-3">{label}</span>
      <button
        type="button"
        onClick={onMinus}
        disabled={!canShrink || loading}
        aria-label={`${label} : retirer un niveau`}
        className="flex h-6 w-6 cursor-pointer items-center justify-center rounded-full text-[12px] font-semibold text-ink-2 transition-colors disabled:cursor-default disabled:opacity-30 hover:enabled:text-ink"
      >
        −
      </button>
      <span
        className="min-w-[14px] text-center text-[11.5px] font-semibold text-ink"
        aria-live="polite"
      >
        {value}
      </span>
      <button
        type="button"
        onClick={onPlus}
        disabled={!canExpand || loading}
        aria-label={`${label} : ajouter un niveau`}
        className="flex h-6 w-6 cursor-pointer items-center justify-center rounded-full text-[12px] font-semibold text-ink-2 transition-colors disabled:cursor-default disabled:opacity-30 hover:enabled:text-ink"
      >
        {loading ? (
          <span className="h-2.5 w-2.5 animate-spin rounded-full border border-ink-3 border-t-transparent" />
        ) : (
          '+'
        )}
      </button>
    </div>
  );
}

// ── Main component ────────────────────────────────────────────────────────────

interface FamilyGraphProps {
  person: Person;
}

export function FamilyGraph({ person }: FamilyGraphProps) {
  const { navigateTo } = usePersonNavigation();
  const graph = useFamilyGraph(person);
  const { layout } = graph;
  const { transform, zoom, isDragging, didDrag, wheelHint, containerRef, zoomBy, fitTo } =
    usePanZoom({ minZoom: 0.1, wheel: 'modifier' });

  const [hoverId, setHoverId] = useState<number | null>(null);
  const [selectedSponsor, setSelectedSponsor] = useState<Sponsor | null>(null);
  const [hoveredLinkId, setHoveredLinkId] = useState<number | null>(null);
  const [navigatingId, setNavigatingId] = useState<number | null>(null);
  const [showDiagonal, setShowDiagonal] = useState(true);

  const rootY = layout.positions[person.id]?.y ?? 0;
  const startYearOf = useMemo(
    () => new Map(layout.allNodes.map((n) => [n.id, n.startYear])),
    [layout.allNodes],
  );

  // Survoler la racine n'estompe rien : seul un autre nœud révèle son chemin vers la racine
  const path = useMemo(
    () =>
      hoverId !== null && hoverId !== person.id
        ? shortestPath(hoverId, person.id, layout.links)
        : null,
    [hoverId, person.id, layout.links],
  );

  const isDiagonal = (l: Sponsor) => {
    const a = layout.positions[l.godFatherId];
    const b = layout.positions[l.godChildId];
    return a !== undefined && b !== undefined && a.y < rootY && b.y > rootY;
  };
  const hasDiagonals = layout.links.some(isDiagonal);

  // Recadre l'arbre à chaque ajout/retrait de niveau
  const nodeCount = layout.allNodes.length;
  const { bounds } = layout;
  useEffect(() => {
    if (graph.initialLoading) return;
    fitTo(bounds, { maxZoom: 1 });
  }, [nodeCount, layout.rowCount, graph.initialLoading, fitTo]);

  if (graph.initialLoading) {
    return <Skeleton className="w-full rounded-xl" style={{ height: 360 }} />;
  }

  if (graph.error && nodeCount <= 1) {
    return (
      <div className="flex h-40 flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-line text-[13px] text-ink-3">
        <p>{graph.error}</p>
        <button
          type="button"
          onClick={graph.retry}
          className="rounded-lg border border-line bg-surface px-3 py-1.5 text-[12.5px] font-medium text-ink-2 hover:border-ink hover:text-ink"
        >
          Réessayer
        </button>
      </div>
    );
  }

  const containerHeight = Math.max(320, Math.min(560, 80 + layout.rowCount * ROW_H));

  return (
    <div
      ref={containerRef}
      className="relative w-full overflow-hidden rounded-xl border border-line"
      style={{
        height: containerHeight,
        background: 'var(--color-bg)',
        backgroundImage: 'radial-gradient(circle, var(--color-line) 1px, transparent 1px)',
        backgroundSize: '22px 22px',
        cursor: isDragging ? 'grabbing' : 'grab',
        userSelect: 'none',
        touchAction: 'none',
      }}
    >
      {/* Profondeur */}
      <div className="absolute left-3 top-3 z-10 flex flex-wrap items-center gap-2">
        <DepthControl
          label="↑ Amont"
          hint="Niveaux de parrains affichés (parrains, parrains des parrains…)"
          value={graph.ancestorDepth}
          canShrink={graph.ancestorDepth > 0}
          canExpand={graph.canExpandAncestors}
          loading={graph.loadingAncestors}
          onMinus={graph.shrinkAncestors}
          onPlus={graph.expandAncestors}
        />
        <DepthControl
          label="↓ Aval"
          hint="Niveaux de fillots affichés (fillots, fillots des fillots…)"
          value={graph.descendantDepth}
          canShrink={graph.descendantDepth > 0}
          canExpand={graph.canExpandDescendants}
          loading={graph.loadingDescendants}
          onMinus={graph.shrinkDescendants}
          onPlus={graph.expandDescendants}
        />
      </div>

      <ZoomControls
        zoomBy={zoomBy}
        onFit={() => {
          fitTo(bounds, { maxZoom: 1 });
        }}
      />

      <GraphStatusBar>
        <span>
          {nodeCount} personne{nodeCount > 1 ? 's' : ''} · {layout.links.length} lien
          {layout.links.length > 1 ? 's' : ''}
        </span>
        <span className="text-ink-4">{Math.round(zoom * 100)}%</span>
        {hasDiagonals && (
          <button
            type="button"
            aria-pressed={showDiagonal}
            title="Liens entre un parrain et un fillot de la personne"
            onClick={() => {
              setShowDiagonal((v) => !v);
            }}
            className={`cursor-pointer rounded px-1.5 py-0.5 text-[10.5px] font-medium transition-colors ${showDiagonal ? 'bg-ink/10 text-ink-2' : 'text-ink-4 hover:text-ink-3'}`}
          >
            ⤢ diagonales
          </button>
        )}
      </GraphStatusBar>

      {graph.error && (
        <div
          role="alert"
          className="absolute left-1/2 top-14 z-20 flex -translate-x-1/2 items-center gap-2 rounded-lg border border-danger/20 bg-surface px-3 py-1.5 text-[12px] text-danger shadow"
        >
          {graph.error}
          <button
            type="button"
            onClick={graph.retry}
            className="font-medium underline underline-offset-2"
          >
            OK
          </button>
        </div>
      )}

      {/* Canvas transformé */}
      <div
        style={{
          position: 'absolute',
          left: 0,
          top: 0,
          transform: transformStyle(transform),
          transformOrigin: '0 0',
        }}
      >
        <svg
          style={{
            position: 'absolute',
            left: 0,
            top: 0,
            width: 1,
            height: 1,
            overflow: 'visible',
          }}
        >
          {layout.links.map((l) => {
            const a = layout.positions[l.godFatherId];
            const b = layout.positions[l.godChildId];
            if (!a || !b) return null;
            const diagonal = isDiagonal(l);
            if (diagonal && !showDiagonal) return null;

            const { x: ax, y: ay } = a;
            const { x: bx, y: by } = b;
            const color = promoColor(startYearOf.get(l.godFatherId) ?? person.startYear);
            const isOnPath = path?.linkIds.has(l.id) ?? false;
            const isHighlighted = hoveredLinkId === l.id || isOnPath;
            const dim = path !== null && !isOnPath;

            // Liens dans une même rangée : arche vers l'extérieur de la racine.
            // Diagonales : courbe latérale pour ne pas se superposer aux liens passant par la racine.
            const isSameLevel = Math.abs(ay - by) < 20;
            const bowDir = isSameLevel
              ? ay < rootY - 10
                ? -1
                : ay > rootY + 10
                  ? 1
                  : l.godChildId === person.id
                    ? -1
                    : 1
              : 0;
            const bow = isSameLevel ? Math.min(100, Math.abs(bx - ax) * 0.45) : 0;
            const diagOffset = diagonal ? Math.abs(by - ay) * 0.45 : 0;
            const c1x = isSameLevel ? ax + (bx - ax) * 0.33 : ax + diagOffset;
            const c1y = isSameLevel ? ay + bowDir * bow : (ay + by) / 2;
            const c2x = isSameLevel ? ax + (bx - ax) * 0.67 : bx + diagOffset;
            const c2y = isSameLevel ? ay + bowDir * bow : (ay + by) / 2;
            const d = `M${String(ax)},${String(ay)} C${String(c1x)},${String(c1y)} ${String(c2x)},${String(c2y)} ${String(bx)},${String(by)}`;

            // Milieu et tangente du bézier cubique à t = 0,5
            const mx = 0.125 * ax + 0.375 * c1x + 0.375 * c2x + 0.125 * bx;
            const my = 0.125 * ay + 0.375 * c1y + 0.375 * c2y + 0.125 * by;
            const tdx = 0.75 * (bx - ax + (c2x - c1x));
            const tdy = 0.75 * (by - ay + (c2y - c1y));
            const tlen = Math.hypot(tdx, tdy) || 1;
            const opacity = dim ? 0.12 : isHighlighted ? 0.95 : 0.55;

            return (
              <g key={l.id}>
                <path
                  d={d}
                  stroke={color}
                  strokeWidth={isHighlighted ? 2.5 : 1.5}
                  fill="none"
                  opacity={opacity}
                  style={{ pointerEvents: 'none' }}
                />
                <polygon
                  points={arrowPoints(mx, my, tdx / tlen, tdy / tlen)}
                  fill={color}
                  opacity={opacity}
                  style={{ pointerEvents: 'none' }}
                />
                <path
                  d={d}
                  stroke="transparent"
                  strokeWidth={14}
                  fill="none"
                  style={{ cursor: 'pointer' }}
                  onMouseEnter={() => {
                    setHoveredLinkId(l.id);
                  }}
                  onMouseLeave={() => {
                    setHoveredLinkId(null);
                  }}
                  onClick={() => {
                    if (!didDrag) setSelectedSponsor(l);
                  }}
                >
                  <title>
                    {l.godFatherName} → {l.godChildName}
                  </title>
                </path>
              </g>
            );
          })}
        </svg>

        {layout.allNodes.map((p) => {
          const pos = layout.positions[p.id];
          if (!pos) return null;
          const isSelf = p.id === person.id;
          return (
            <PersonGraphNode
              key={p.id}
              person={p}
              cx={pos.x}
              cy={pos.y}
              diameter={isSelf ? NODE_D_SELF : NODE_D}
              isSelf={isSelf}
              dim={path !== null && !path.nodeIds.has(p.id)}
              loading={navigatingId === p.id}
              {...(isSelf
                ? {}
                : {
                    actionLabel: `Voir la fiche de ${p.fullName}`,
                    onClick: () => {
                      if (didDrag) return;
                      setNavigatingId(p.id);
                      void navigateTo(p.id);
                    },
                  })}
              onHoverChange={(h) => {
                setHoverId(h ? p.id : null);
              }}
            />
          );
        })}
      </div>

      {selectedSponsor && (
        <SponsorInfoCard
          sponsor={selectedSponsor}
          godFatherStartYear={startYearOf.get(selectedSponsor.godFatherId)}
          onClose={() => {
            setSelectedSponsor(null);
          }}
        />
      )}

      <WheelHint visible={wheelHint} />
    </div>
  );
}
