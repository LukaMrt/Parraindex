import { useMemo, useState } from 'react';
import { usePersonNavigation } from '../../../hooks/usePersonNavigation';
import { Button, Skeleton } from '../../../components/ui';
import { PersonGraphNode } from '../../../components/graph/PersonGraphNode';
import { SponsorInfoCard } from '../../../components/graph/SponsorInfoCard';
import { promoColor } from '../../../lib/colors';
import { usePanZoom } from '../../../hooks/usePanZoom';
import { isNeighbor } from '../../person/familyGraphLayout';
import type { Person } from '../../../types/person';
import type { Sponsor } from '../../../types/sponsor';
import type { SponsorLink } from '../useSponsorsGraph';

type FilterMode = 'all' | 'connected' | 'isolated';

const COL_W = 140;
const ROW_H = 130;
const NODE_D = 44;

interface NodePos {
  x: number;
  y: number;
  year: number;
}

interface Layout {
  nodes: Person[];
  links: SponsorLink[];
  indirectLinks: (SponsorLink & { hops: number })[];
  positions: Record<number, NodePos>;
  yearList: number[];
  width: number;
  height: number;
}

function computeLayout(persons: Person[], allLinks: SponsorLink[], filterMode: FilterMode): Layout {
  const visibleIds = new Set(persons.map((p) => p.id));
  let links = allLinks.filter((l) => visibleIds.has(l.godFatherId) && visibleIds.has(l.godChildId));
  let nodes = persons;

  if (filterMode === 'connected') {
    const linkedIds = new Set<number>();
    links.forEach((l) => {
      linkedIds.add(l.godFatherId);
      linkedIds.add(l.godChildId);
    });
    nodes = nodes.filter((p) => linkedIds.has(p.id));
  } else if (filterMode === 'isolated') {
    const linkedIds = new Set<number>();
    allLinks.forEach((l) => {
      linkedIds.add(l.godFatherId);
      linkedIds.add(l.godChildId);
    });
    nodes = nodes.filter((p) => !linkedIds.has(p.id));
    links = [];
  }

  // Liens indirects : BFS depuis chaque nœud visible à travers les nœuds cachés
  const visibleNodeIds = new Set(nodes.map((p) => p.id));
  const childrenOf: Record<number, number[]> = {};
  allLinks.forEach((l) => {
    childrenOf[l.godFatherId] ??= [];
    childrenOf[l.godFatherId].push(l.godChildId);
  });
  const directKey = new Set(links.map((l) => `${l.godFatherId}-${l.godChildId}`));
  const indirectLinks: (SponsorLink & { hops: number })[] = [];
  const indirectKey = new Set<string>();

  nodes.forEach((src) => {
    const stack: { id: number; hops: number }[] = [{ id: src.id, hops: 0 }];
    const seen = new Set([src.id]);
    while (stack.length) {
      const item = stack.pop();
      if (!item) break;
      const { id, hops } = item;
      for (const childId of childrenOf[id] ?? []) {
        if (seen.has(childId)) continue;
        seen.add(childId);
        if (visibleNodeIds.has(childId)) {
          if (hops >= 1) {
            const k = `${src.id}-${childId}`;
            if (!directKey.has(k) && !indirectKey.has(k)) {
              indirectKey.add(k);
              indirectLinks.push({ godFatherId: src.id, godChildId: childId, hops: hops + 1 });
            }
          }
        } else {
          stack.push({ id: childId, hops: hops + 1 });
        }
      }
    }
  });

  const byYear: Record<number, Person[]> = {};
  nodes.forEach((p) => {
    byYear[p.startYear] ??= [];
    byYear[p.startYear].push(p);
  });
  Object.values(byYear).forEach((list) =>
    list.sort((a, b) => a.lastName.localeCompare(b.lastName)),
  );

  const yearList = Object.keys(byYear)
    .map(Number)
    .sort((a, b) => a - b);
  const positions: Record<number, NodePos> = {};
  yearList.forEach((year, rowIdx) => {
    byYear[year].forEach((p, colIdx) => {
      positions[p.id] = { x: colIdx * COL_W + 60, y: rowIdx * ROW_H + 80, year };
    });
  });

  const maxCols = Math.max(...Object.values(byYear).map((l) => l.length), 1);
  return {
    nodes,
    links,
    indirectLinks,
    positions,
    yearList,
    width: maxCols * COL_W + 120,
    height: yearList.length * ROW_H + 160,
  };
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
  const {
    pan,
    zoom,
    isDragging,
    didDrag,
    containerRef,
    setZoom,
    handleMouseDown,
    handleMouseMove,
    handleMouseUp,
    resetView,
  } = usePanZoom({ dragBlockSelector: '[data-node]', minZoom: 0.3 });

  const layout = useMemo(
    () => computeLayout(persons, allLinks, filterMode),
    [persons, allLinks, filterMode],
  );

  const handleNodeClick = (id: number) => {
    if (!didDrag) {
      setNavigatingId(id);
      void navigateTo(id);
    }
  };

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
          <Skeleton className="h-7 w-16 rounded-full" />
        </div>
        <Skeleton className="flex-1 rounded-lg" style={{ minHeight: 440 }} />
      </div>
    );
  }

  return (
    <div
      className="relative w-full overflow-hidden rounded-xl border border-line"
      style={{
        height: 'calc(100vh - 240px)',
        minHeight: 520,
        background: 'var(--color-surface)',
        backgroundImage: 'radial-gradient(circle, var(--color-line) 1px, transparent 1px)',
        backgroundSize: '24px 24px',
        cursor: isDragging ? 'grabbing' : 'grab',
      }}
      ref={containerRef}
      onMouseDown={handleMouseDown}
      onMouseMove={handleMouseMove}
      onMouseUp={handleMouseUp}
      onMouseLeave={handleMouseUp}
    >
      {/* Filtres (haut-gauche) */}
      <div
        className="absolute left-3 top-3 z-10 flex cursor-default items-center gap-2"
        onMouseDown={(e) => {
          e.stopPropagation();
        }}
      >
        <span className="text-[11.5px] text-ink-3">Afficher :</span>
        {(['all', 'connected', 'isolated'] as FilterMode[]).map((m) => (
          <Button
            key={m}
            variant={filterMode === m ? 'pill-active' : 'pill-neutral'}
            size="sm"
            onClick={(e) => {
              e.stopPropagation();
              setFilterMode(m);
            }}
          >
            {m === 'all' ? 'Tous' : m === 'connected' ? 'Avec liens' : 'Sans liens'}
          </Button>
        ))}
      </div>

      {/* Zoom controls (haut-droite) */}
      <div
        className="absolute right-3 top-3 z-10 flex cursor-default flex-col gap-1.5"
        onMouseDown={(e) => {
          e.stopPropagation();
        }}
      >
        {(
          [
            {
              sym: '+',
              action: () => {
                setZoom((z) => Math.min(2.5, z * 1.1));
              },
            },
            {
              sym: '−',
              action: () => {
                setZoom((z) => Math.max(0.3, z / 1.1));
              },
            },
            {
              sym: '⤓',
              action: resetView,
            },
          ] as const
        ).map(({ sym, action }) => (
          <button
            key={sym}
            onClick={(e) => {
              e.stopPropagation();
              action();
            }}
            className="flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg border border-line bg-surface text-sm font-semibold text-ink-2 transition-colors hover:border-ink hover:text-ink"
          >
            {sym}
          </button>
        ))}
      </div>

      {/* Status bar (bas-gauche) */}
      <div
        className="absolute bottom-3 left-3 z-10 flex cursor-default items-center gap-3 rounded-lg border border-line bg-surface px-3 py-1.5 text-[11.5px] text-ink-3"
        onMouseDown={(e) => {
          e.stopPropagation();
        }}
      >
        <span>
          {layout.nodes.length} nœuds · {layout.links.length} liens
          {layout.indirectLinks.length > 0 && ` · ${layout.indirectLinks.length} indirects`}
        </span>
        <span className="text-ink-4">{Math.round(zoom * 100)}%</span>
      </div>

      {/* Canvas transformé */}
      <div
        style={{
          transform: `translate(${pan.x}px, ${pan.y}px) scale(${zoom})`,
          transformOrigin: '0 0',
          width: layout.width,
          height: layout.height,
          position: 'relative',
        }}
      >
        {/* Guides de promo */}
        {layout.yearList.map((year, rowIdx) => {
          const y = rowIdx * ROW_H + 80;
          const color = promoColor(year);
          return (
            <div key={year}>
              <div
                style={{
                  position: 'absolute',
                  left: 0,
                  top: y - 20,
                  width: layout.width,
                  height: 1,
                  background: `${color}30`,
                }}
              />
              <div
                style={{
                  position: 'absolute',
                  left: 8,
                  top: y - 32,
                  fontSize: 10,
                  fontWeight: 600,
                  letterSpacing: '0.06em',
                  textTransform: 'uppercase',
                  color,
                  background: 'var(--color-surface)',
                  padding: '2px 8px',
                  borderRadius: 4,
                }}
              >
                Promo {year}
              </div>
            </div>
          );
        })}

        {/* Arêtes SVG */}
        <svg
          style={{
            position: 'absolute',
            inset: 0,
            width: '100%',
            height: '100%',
          }}
        >
          {layout.indirectLinks.map((l, i) => {
            const a = layout.positions[l.godFatherId];
            const b = layout.positions[l.godChildId];
            if (!a || !b) return null;
            const r = NODE_D / 2;
            const ax = a.x + r,
              ay = a.y + r;
            const bx = b.x + r,
              by = b.y + r;
            const cy = (ay + by) / 2;
            const dim = hoverId !== null;
            return (
              <path
                key={`i-${i}`}
                d={`M${ax},${ay} C${ax},${cy} ${bx},${cy} ${bx},${by}`}
                stroke={promoColor(a.year)}
                strokeWidth={1.25}
                fill="none"
                strokeDasharray="3 4"
                opacity={dim ? 0.08 : 0.4}
                strokeLinecap="round"
              />
            );
          })}
          {layout.links.map((l, i) => {
            const a = layout.positions[l.godFatherId];
            const b = layout.positions[l.godChildId];
            if (!a || !b) return null;
            const r = NODE_D / 2;
            const ax = a.x + r,
              ay = a.y + r;
            const bx = b.x + r,
              by = b.y + r;
            const cy = (ay + by) / 2;
            const isHighlighted =
              hoveredLinkId === l.id ||
              (hoverId !== null && (l.godFatherId === hoverId || l.godChildId === hoverId));
            const dim = hoverId !== null && !isHighlighted;
            const dPath = `M${ax},${ay} C${ax},${cy} ${bx},${cy} ${bx},${by}`;
            return (
              <g key={i}>
                <path
                  d={dPath}
                  style={{ pointerEvents: 'none' }}
                  stroke={promoColor(a.year)}
                  strokeWidth={isHighlighted ? 2.5 : 1.5}
                  fill="none"
                  opacity={dim ? 0.1 : isHighlighted ? 0.95 : 0.5}
                />
                <path
                  d={dPath}
                  stroke="transparent"
                  strokeWidth={12}
                  fill="none"
                  style={{ cursor: 'pointer' }}
                  onMouseEnter={() => {
                    setHoveredLinkId(l.id);
                  }}
                  onMouseLeave={() => {
                    setHoveredLinkId(null);
                  }}
                  onClick={() => {
                    const gf = layout.nodes.find((n) => n.id === l.godFatherId);
                    const gc = layout.nodes.find((n) => n.id === l.godChildId);
                    setSelectedSponsor({
                      id: l.id,
                      godFatherId: l.godFatherId,
                      godFatherName: gf?.fullName ?? String(l.godFatherId),
                      godChildId: l.godChildId,
                      godChildName: gc?.fullName ?? String(l.godChildId),
                      type: 'UNKNOWN',
                      date: null,
                    });
                  }}
                />
              </g>
            );
          })}
        </svg>

        {/* Nœuds */}
        {layout.nodes.map((p) => {
          const pos = layout.positions[p.id];
          if (!pos) return null;
          const dim =
            hoverId !== null && hoverId !== p.id && !isNeighbor(p.id, hoverId, layout.links);
          return (
            <PersonGraphNode
              key={p.id}
              person={p}
              diameter={NODE_D}
              dim={dim}
              loading={navigatingId === p.id}
              dataAttr="data-node"
              style={{ position: 'absolute', left: pos.x, top: pos.y }}
              onClick={() => {
                handleNodeClick(p.id);
              }}
              onMouseEnter={() => {
                setHoverId(p.id);
              }}
              onMouseLeave={() => {
                setHoverId(null);
              }}
            />
          );
        })}
      </div>

      {selectedSponsor && (
        <SponsorInfoCard
          summary={selectedSponsor}
          godFatherStartYear={
            layout.nodes.find((n) => n.id === selectedSponsor.godFatherId)?.startYear
          }
          position="top-right"
          onClose={() => {
            setSelectedSponsor(null);
          }}
        />
      )}
    </div>
  );
}
