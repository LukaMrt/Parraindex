import { useEffect, useMemo, useRef, useState, type MouseEvent } from 'react';
import { useNavigate } from 'react-router';
import { Avatar, Button, Skeleton } from '../../../components/ui';
import { promoColor } from '../../../lib/colors';
import type { PersonSummary } from '../../../types/person';
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
  nodes: PersonSummary[];
  links: SponsorLink[];
  indirectLinks: (SponsorLink & { hops: number })[];
  positions: Record<number, NodePos>;
  yearList: number[];
  width: number;
  height: number;
}

function computeLayout(
  persons: PersonSummary[],
  allLinks: SponsorLink[],
  filterMode: FilterMode,
): Layout {
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

  const byYear: Record<number, PersonSummary[]> = {};
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
  persons: PersonSummary[];
  links: SponsorLink[];
  loading: boolean;
}

export function TreeView({ persons, links: allLinks, loading }: Props) {
  const navigate = useNavigate();

  const [pan, setPan] = useState({ x: 0, y: 0 });
  const [zoom, setZoom] = useState(1);
  const [isDragging, setIsDragging] = useState(false);
  const [filterMode, setFilterMode] = useState<FilterMode>('all');
  const [showLabels, setShowLabels] = useState(true);
  const dragState = useRef({ active: false, startX: 0, startY: 0, originX: 0, originY: 0 });
  const didDrag = useRef(false);
  const containerRef = useRef<HTMLDivElement>(null);

  const layout = useMemo(
    () => computeLayout(persons, allLinks, filterMode),
    [persons, allLinks, filterMode],
  );

  const handleMouseDown = (e: MouseEvent) => {
    if ((e.target as HTMLElement).closest('[data-node]')) return;
    didDrag.current = false;
    dragState.current = {
      active: true,
      startX: e.clientX,
      startY: e.clientY,
      originX: pan.x,
      originY: pan.y,
    };
  };

  const handleMouseMove = (e: MouseEvent) => {
    if (!dragState.current.active) return;
    didDrag.current = true;
    if (!isDragging) setIsDragging(true);
    setPan({
      x: dragState.current.originX + (e.clientX - dragState.current.startX),
      y: dragState.current.originY + (e.clientY - dragState.current.startY),
    });
  };

  const handleMouseUp = () => {
    dragState.current.active = false;
    setIsDragging(false);
  };

  useEffect(() => {
    const el = containerRef.current;
    if (!el) return;
    const onWheel = (e: globalThis.WheelEvent) => {
      e.preventDefault();
      setZoom((z) => Math.max(0.3, Math.min(2.5, e.deltaY < 0 ? z * 1.1 : z / 1.1)));
    };
    el.addEventListener('wheel', onWheel, { passive: false });
    return () => {
      el.removeEventListener('wheel', onWheel);
    };
  }, []);

  const handleNodeClick = (id: number) => {
    if (!didDrag.current) {
      void navigate(`/person/${id}`);
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
        <Button
          variant={showLabels ? 'pill-active' : 'pill-neutral'}
          size="sm"
          onClick={(e) => {
            e.stopPropagation();
            setShowLabels((v) => !v);
          }}
        >
          Noms
        </Button>
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
              action: () => {
                setZoom(1);
                setPan({ x: 0, y: 0 });
              },
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
            pointerEvents: 'none',
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
            return (
              <path
                key={`i-${i}`}
                d={`M${ax},${ay} C${ax},${cy} ${bx},${cy} ${bx},${by}`}
                stroke={promoColor(a.year)}
                strokeWidth={1.25}
                fill="none"
                strokeDasharray="3 4"
                opacity={0.4}
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
            return (
              <path
                key={i}
                d={`M${ax},${ay} C${ax},${cy} ${bx},${cy} ${bx},${by}`}
                stroke={promoColor(a.year)}
                strokeWidth={1.5}
                fill="none"
                opacity={0.5}
              />
            );
          })}
        </svg>

        {/* Nœuds */}
        {layout.nodes.map((p) => {
          const pos = layout.positions[p.id];
          if (!pos) return null;
          const color = promoColor(p.startYear);
          return (
            <div
              key={p.id}
              data-node
              onClick={() => {
                handleNodeClick(p.id);
              }}
              style={{
                position: 'absolute',
                left: pos.x,
                top: pos.y,
                width: NODE_D,
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                gap: 4,
                cursor: 'pointer',
                transition: 'transform 0.15s',
              }}
              onMouseEnter={(e) => {
                (e.currentTarget as HTMLElement).style.transform = 'scale(1.08)';
              }}
              onMouseLeave={(e) => {
                (e.currentTarget as HTMLElement).style.transform = '';
              }}
            >
              <div
                style={{
                  width: NODE_D,
                  height: NODE_D,
                  borderRadius: '50%',
                  overflow: 'hidden',
                  border: `2px solid ${color}`,
                  boxShadow: '0 2px 6px rgba(0,0,0,0.06)',
                  background: 'white',
                  flexShrink: 0,
                }}
              >
                <Avatar person={p} size={NODE_D} fill />
              </div>
              {showLabels && (
                <div
                  style={{
                    fontSize: 10.5,
                    fontWeight: 500,
                    color: 'var(--color-ink)',
                    textAlign: 'center',
                    lineHeight: 1.15,
                    maxWidth: 80,
                    whiteSpace: 'nowrap',
                    overflow: 'hidden',
                    textOverflow: 'ellipsis',
                    background: 'var(--color-surface)',
                    padding: '2px 5px',
                    borderRadius: 4,
                  }}
                >
                  {p.firstName}
                </div>
              )}
            </div>
          );
        })}
      </div>
    </div>
  );
}
