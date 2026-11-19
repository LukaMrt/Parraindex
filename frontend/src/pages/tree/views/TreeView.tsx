import { useEffect, useMemo, useState } from 'react';
import { usePersonNavigation } from '../../../hooks/usePersonNavigation';
import { transformStyle, usePanZoom } from '../../../hooks/usePanZoom';
import { Button, EmptyState, Skeleton } from '../../../components/ui';
import { PersonGraphNode } from '../../../components/graph/PersonGraphNode';
import { SponsorInfoCard } from '../../../components/graph/SponsorInfoCard';
import { GraphStatusBar, ZoomControls, arrowPoints } from '../../../components/graph/GraphControls';
import { promoColor } from '../../../lib/colors';
import type { Person } from '../../../types/person';
import type { Sponsor } from '../../../types/sponsor';
import type { SponsorLink } from '../useSponsorsGraph';

type FilterMode = 'all' | 'connected' | 'isolated';

const COL_W = 130;
const ROW_H = 130;
const NODE_D = 44;
const MARGIN_X = 70;
const MARGIN_Y = 90;

interface IndirectLink {
  godFatherId: number;
  godChildId: number;
  /** Nombre de liens entre les deux personnes (≥ 2) */
  hops: number;
}

interface Layout {
  nodes: Person[];
  links: SponsorLink[];
  indirectLinks: IndirectLink[];
  /** Centre de chaque nœud */
  positions: Map<number, { x: number; y: number; year: number }>;
  rows: { year: number; y: number }[];
  neighbors: Map<number, Set<number>>;
  width: number;
  height: number;
}

const mean = (xs: number[]) => xs.reduce((s, x) => s + x, 0) / xs.length;

export function computeTreeLayout(
  persons: Person[],
  allLinks: SponsorLink[],
  filterMode: FilterMode,
): Layout {
  const visibleIds = new Set(persons.map((p) => p.id));
  const directLinks = allLinks.filter(
    (l) => visibleIds.has(l.godFatherId) && visibleIds.has(l.godChildId),
  );
  const directKey = new Set(
    directLinks.map((l) => `${String(l.godFatherId)}-${String(l.godChildId)}`),
  );

  const childrenOf = new Map<number, number[]>();
  for (const l of allLinks) {
    const list = childrenOf.get(l.godFatherId) ?? [];
    list.push(l.godChildId);
    childrenOf.set(l.godFatherId, list);
  }

  // Liens indirects : parcours en largeur depuis chaque nœud visible à travers les nœuds
  // masqués par les filtres (le plus court chemin donne le bon nombre d'intermédiaires).
  const indirectLinks: IndirectLink[] = [];
  const indirectKey = new Set<string>();
  for (const src of persons) {
    const seen = new Set([src.id]);
    const queue: { id: number; hops: number }[] = [{ id: src.id, hops: 0 }];
    for (const item of queue) {
      for (const childId of childrenOf.get(item.id) ?? []) {
        if (seen.has(childId)) continue;
        seen.add(childId);
        if (!visibleIds.has(childId)) {
          queue.push({ id: childId, hops: item.hops + 1 });
          continue;
        }
        const k = `${String(src.id)}-${String(childId)}`;
        if (item.hops >= 1 && !directKey.has(k) && !indirectKey.has(k)) {
          indirectKey.add(k);
          indirectLinks.push({ godFatherId: src.id, godChildId: childId, hops: item.hops + 1 });
        }
      }
    }
  }

  // « Avec liens » / « Sans liens » forment une partition des personnes affichées :
  // les deux se basent sur les liens visibles (directs ou indirects).
  const linkedIds = new Set<number>();
  for (const l of [...directLinks, ...indirectLinks]) {
    linkedIds.add(l.godFatherId);
    linkedIds.add(l.godChildId);
  }
  const nodes =
    filterMode === 'connected'
      ? persons.filter((p) => linkedIds.has(p.id))
      : filterMode === 'isolated'
        ? persons.filter((p) => !linkedIds.has(p.id))
        : persons;
  const links = filterMode === 'isolated' ? [] : directLinks;
  const indirect = filterMode === 'isolated' ? [] : indirectLinks;

  // Voisinage (pour le survol, en O(1) par nœud)
  const neighbors = new Map<number, Set<number>>();
  const addNeighbor = (a: number, b: number) => {
    const set = neighbors.get(a) ?? new Set<number>();
    set.add(b);
    neighbors.set(a, set);
  };
  for (const l of links) {
    addNeighbor(l.godFatherId, l.godChildId);
    addNeighbor(l.godChildId, l.godFatherId);
  }

  // Une rangée par promo
  const byYear = new Map<number, Person[]>();
  for (const p of nodes) {
    const list = byYear.get(p.startYear) ?? [];
    list.push(p);
    byYear.set(p.startYear, list);
  }
  const years = [...byYear.keys()].sort((a, b) => a - b);
  const rowsOfPersons = years.map((y) =>
    [...(byYear.get(y) ?? [])].sort((a, b) => a.lastName.localeCompare(b.lastName, 'fr')),
  );

  const parentsOf = new Map<number, number[]>();
  const kidsOf = new Map<number, number[]>();
  for (const l of [...links, ...indirect]) {
    parentsOf.set(l.godChildId, [...(parentsOf.get(l.godChildId) ?? []), l.godFatherId]);
    kidsOf.set(l.godFatherId, [...(kidsOf.get(l.godFatherId) ?? []), l.godChildId]);
  }

  // Ordonnancement barycentrique (une passe descendante puis une montante) : chaque
  // personne se rapproche de ses parrains / fillots → beaucoup moins de croisements.
  const xOf = new Map<number, number>();
  const assign = (row: Person[]) => {
    row.forEach((p, i) => xOf.set(p.id, MARGIN_X + i * COL_W));
  };
  const reorder = (row: Person[], related: Map<number, number[]>) => {
    const keyed = row.map((p, i) => {
      const xs = (related.get(p.id) ?? [])
        .map((id) => xOf.get(id))
        .filter((x): x is number => x !== undefined);
      // Sans voisin placé : garde sa position relative actuelle
      return { p, k: xs.length > 0 ? mean(xs) : (xOf.get(p.id) ?? MARGIN_X + i * COL_W) };
    });
    keyed.sort((a, b) => a.k - b.k);
    return keyed.map(({ p }) => p);
  };

  rowsOfPersons.forEach(assign);
  for (let r = 1; r < rowsOfPersons.length; r++) {
    const row = rowsOfPersons[r];
    if (!row) continue;
    rowsOfPersons[r] = reorder(row, parentsOf);
    assign(rowsOfPersons[r] ?? []);
  }
  for (let r = rowsOfPersons.length - 2; r >= 0; r--) {
    const row = rowsOfPersons[r];
    if (!row) continue;
    rowsOfPersons[r] = reorder(row, kidsOf);
    assign(rowsOfPersons[r] ?? []);
  }

  const positions = new Map<number, { x: number; y: number; year: number }>();
  const rows = years.map((year, rIdx) => ({ year, y: MARGIN_Y + rIdx * ROW_H }));
  rowsOfPersons.forEach((row, rIdx) => {
    const year = years[rIdx] ?? 0;
    row.forEach((p) => {
      positions.set(p.id, { x: xOf.get(p.id) ?? 0, y: MARGIN_Y + rIdx * ROW_H, year });
    });
  });

  const maxCols = Math.max(1, ...rowsOfPersons.map((r) => r.length));
  return {
    nodes,
    links,
    indirectLinks: indirect,
    positions,
    rows,
    neighbors,
    width: MARGIN_X * 2 + (maxCols - 1) * COL_W,
    height: MARGIN_Y + years.length * ROW_H,
  };
}

/** Chemin vertical (ou arche si les deux personnes sont de la même promo) + flèche */
function edgeGeometry(ax: number, ay: number, bx: number, by: number) {
  if (Math.abs(ay - by) < 1) {
    const bow = Math.min(90, Math.abs(bx - ax) * 0.35) + 20;
    const c1x = ax + (bx - ax) * 0.33;
    const c2x = ax + (bx - ax) * 0.67;
    const cy = ay - bow;
    const d = `M${String(ax)},${String(ay)} C${String(c1x)},${String(cy)} ${String(c2x)},${String(cy)} ${String(bx)},${String(by)}`;
    const mx = 0.125 * ax + 0.375 * c1x + 0.375 * c2x + 0.125 * bx;
    const my = 0.25 * ay + 0.75 * cy;
    const len = Math.abs(bx - ax) || 1;
    return { d, arrow: arrowPoints(mx, my, (bx - ax) / len, 0) };
  }
  const cy = (ay + by) / 2;
  const d = `M${String(ax)},${String(ay)} C${String(ax)},${String(cy)} ${String(bx)},${String(cy)} ${String(bx)},${String(by)}`;
  const tdx = 1.5 * (bx - ax);
  const tdy = 0.75 * (by - ay);
  const tlen = Math.hypot(tdx, tdy) || 1;
  return { d, arrow: arrowPoints((ax + bx) / 2, cy, tdx / tlen, tdy / tlen) };
}

interface Props {
  persons: Person[];
  links: SponsorLink[];
  loading: boolean;
}

export function TreeView({ persons, links: allLinks, loading }: Props) {
  const { navigateTo } = usePersonNavigation();

  const [filterMode, setFilterMode] = useState<FilterMode>('all');
  const [hoverId, setHoverId] = useState<number | null>(null);
  const [selectedSponsor, setSelectedSponsor] = useState<Sponsor | null>(null);
  const [hoveredLinkId, setHoveredLinkId] = useState<number | null>(null);
  const [navigatingId, setNavigatingId] = useState<number | null>(null);
  const { transform, zoom, isDragging, didDrag, containerRef, zoomBy, fitTo } = usePanZoom({
    minZoom: 0.15,
  });

  const layout = useMemo(
    () => computeTreeLayout(persons, allLinks, filterMode),
    [persons, allLinks, filterMode],
  );

  const bounds = useMemo(
    // Marge haute supplémentaire : la barre de filtres recouvre le haut du canvas
    () => ({
      x: 0,
      y: MARGIN_Y - 110,
      width: layout.width,
      height: layout.height - MARGIN_Y + 120,
    }),
    [layout.width, layout.height],
  );

  // Cadre le graphe quand son contenu change (filtres, chargement)
  useEffect(() => {
    if (loading) return;
    fitTo(bounds, { maxZoom: 1, padding: 24 });
  }, [bounds, loading, fitTo]);

  if (loading) {
    return (
      <div
        className="flex w-full flex-col gap-3 rounded-xl border border-line bg-surface p-6"
        style={{ minHeight: 520 }}
      >
        <div className="flex gap-2">
          <Skeleton className="h-7 w-16 rounded-full" />
          <Skeleton className="h-7 w-24 rounded-full" />
          <Skeleton className="h-7 w-24 rounded-full" />
        </div>
        <Skeleton className="flex-1 rounded-lg" style={{ minHeight: 440 }} />
      </div>
    );
  }

  const hoverNeighbors = hoverId !== null ? layout.neighbors.get(hoverId) : undefined;

  return (
    <div
      ref={containerRef}
      className="relative w-full overflow-hidden rounded-xl border border-line"
      style={{
        height: 'calc(100vh - 240px)',
        minHeight: 520,
        background: 'var(--color-surface)',
        backgroundImage: 'radial-gradient(circle, var(--color-line) 1px, transparent 1px)',
        backgroundSize: '24px 24px',
        cursor: isDragging ? 'grabbing' : 'grab',
        userSelect: 'none',
        touchAction: 'none',
      }}
    >
      {/* Filtres (haut-gauche) */}
      <div className="absolute left-3 top-3 z-10 flex flex-wrap items-center gap-2 pr-14">
        <span className="text-[11.5px] text-ink-3">Afficher :</span>
        {(['all', 'connected', 'isolated'] as FilterMode[]).map((m) => (
          <Button
            key={m}
            variant={filterMode === m ? 'pill-active' : 'pill-neutral'}
            size="sm"
            aria-pressed={filterMode === m}
            onClick={() => {
              setFilterMode(m);
              setSelectedSponsor(null);
            }}
          >
            {m === 'all' ? 'Tous' : m === 'connected' ? 'Avec liens' : 'Sans liens'}
          </Button>
        ))}
      </div>

      <ZoomControls
        zoomBy={zoomBy}
        onFit={() => {
          fitTo(bounds, { maxZoom: 1, padding: 24 });
        }}
      />

      <GraphStatusBar>
        <span>
          {layout.nodes.length} personne{layout.nodes.length > 1 ? 's' : ''} · {layout.links.length}{' '}
          lien{layout.links.length > 1 ? 's' : ''}
          {layout.indirectLinks.length > 0 &&
            ` · ${String(layout.indirectLinks.length)} indirect${layout.indirectLinks.length > 1 ? 's' : ''} (pointillés)`}
        </span>
        <span className="text-ink-4">{Math.round(zoom * 100)}%</span>
      </GraphStatusBar>

      {layout.nodes.length === 0 && (
        <div className="absolute inset-0 flex items-center justify-center">
          <EmptyState title="Aucune personne" description="Aucun résultat pour ces filtres." />
        </div>
      )}

      {/* Étiquettes de promo : restent visibles à gauche quel que soit le déplacement */}
      <div className="pointer-events-none absolute inset-0 z-[5] overflow-hidden">
        {layout.rows.map(({ year, y }) => {
          const top = transform.y + (y - NODE_D / 2 - 26) * transform.k;
          // Masquée sous la barre de filtres plutôt que de la chevaucher
          if (top < 44) return null;
          return (
            <div
              key={year}
              className="absolute left-2 rounded px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.06em]"
              style={{ top, color: promoColor(year), background: 'var(--color-surface)' }}
            >
              Promo {year}
            </div>
          );
        })}
      </div>

      {/* Canvas transformé */}
      <div
        style={{
          position: 'absolute',
          left: 0,
          top: 0,
          width: layout.width,
          height: layout.height,
          transform: transformStyle(transform),
          transformOrigin: '0 0',
        }}
      >
        {/* Guides de promo */}
        {layout.rows.map(({ year, y }) => (
          <div
            key={year}
            style={{
              position: 'absolute',
              left: 0,
              top: y - NODE_D / 2 - 14,
              width: layout.width,
              height: 1,
              background: `${promoColor(year)}30`,
            }}
          />
        ))}

        <svg
          style={{
            position: 'absolute',
            inset: 0,
            width: '100%',
            height: '100%',
            overflow: 'visible',
          }}
        >
          {layout.indirectLinks.map((l) => {
            const a = layout.positions.get(l.godFatherId);
            const b = layout.positions.get(l.godChildId);
            if (!a || !b) return null;
            const { d } = edgeGeometry(a.x, a.y, b.x, b.y);
            return (
              <path
                key={`i-${String(l.godFatherId)}-${String(l.godChildId)}`}
                d={d}
                stroke={promoColor(a.year)}
                strokeWidth={1.25}
                fill="none"
                strokeDasharray="3 4"
                opacity={hoverId !== null ? 0.08 : 0.4}
                strokeLinecap="round"
              >
                <title>
                  Lien indirect ({l.hops - 1} intermédiaire{l.hops > 2 ? 's' : ''} masqué
                  {l.hops > 2 ? 's' : ''})
                </title>
              </path>
            );
          })}
          {layout.links.map((l) => {
            const a = layout.positions.get(l.godFatherId);
            const b = layout.positions.get(l.godChildId);
            if (!a || !b) return null;
            const { d, arrow } = edgeGeometry(a.x, a.y, b.x, b.y);
            const isHighlighted =
              hoveredLinkId === l.id ||
              (hoverId !== null && (l.godFatherId === hoverId || l.godChildId === hoverId));
            const dim = hoverId !== null && !isHighlighted;
            const color = promoColor(a.year);
            const opacity = dim ? 0.1 : isHighlighted ? 0.95 : 0.5;
            return (
              <g key={l.id}>
                <path
                  d={d}
                  style={{ pointerEvents: 'none' }}
                  stroke={color}
                  strokeWidth={isHighlighted ? 2.5 : 1.5}
                  fill="none"
                  opacity={opacity}
                />
                <polygon
                  points={arrow}
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

        {layout.nodes.map((p) => {
          const pos = layout.positions.get(p.id);
          if (!pos) return null;
          const dim = hoverId !== null && hoverId !== p.id && !(hoverNeighbors?.has(p.id) ?? false);
          return (
            <PersonGraphNode
              key={p.id}
              person={p}
              cx={pos.x}
              cy={pos.y}
              diameter={NODE_D}
              dim={dim}
              loading={navigatingId === p.id}
              actionLabel={`Voir la fiche de ${p.fullName}`}
              onClick={() => {
                if (didDrag) return;
                setNavigatingId(p.id);
                void navigateTo(p.id);
              }}
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
          godFatherStartYear={layout.positions.get(selectedSponsor.godFatherId)?.year}
          onClose={() => {
            setSelectedSponsor(null);
          }}
        />
      )}
    </div>
  );
}
