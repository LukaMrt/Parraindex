import { useEffect, useMemo, useRef, useState, type MouseEvent } from 'react';
import { useNavigate } from 'react-router';
import { Avatar, Skeleton } from '../../components/ui';
import { getPerson } from '../../lib/api/persons';
import { promoColor } from '../../lib/colors';
import type { Person } from '../../types/person';
import type { PersonSummary } from '../../types/person';

// ── Constants ─────────────────────────────────────────────────────────────────

const COL_W = 110;
const ROW_H = 110;
const NODE_D = 44;
const NODE_D_SELF = 56;

// ── Types ─────────────────────────────────────────────────────────────────────

interface GraphLink {
  godFatherId: number;
  godChildId: number;
}

interface NodePos {
  x: number; // relative to canvas center
  y: number;
}

interface Layout {
  allNodes: PersonSummary[];
  positions: Record<number, NodePos>;
  links: GraphLink[];
  canvasWidth: number;
  canvasHeight: number;
  rowCount: number;
}

// ── Helpers ───────────────────────────────────────────────────────────────────

function toSummary(p: Person): PersonSummary {
  return {
    id: p.id,
    firstName: p.firstName,
    lastName: p.lastName,
    fullName: p.fullName,
    picture: p.picture,
    startYear: p.startYear,
  };
}

function isNeighbor(id: number, pivotId: number, links: GraphLink[]): boolean {
  return links.some(
    (l) =>
      (l.godFatherId === pivotId && l.godChildId === id) ||
      (l.godChildId === pivotId && l.godFatherId === id),
  );
}

function fetchPersons(ids: number[]): Promise<Map<number, Person>> {
  return Promise.all(ids.map((id) => getPerson(id))).then((results) => {
    const map = new Map<number, Person>();
    for (const r of results) if (r.ok) map.set(r.data.id, r.data);
    return map;
  });
}

// ── Layout computation ────────────────────────────────────────────────────────

function computeLayout(
  root: PersonSummary,
  ancestorGens: PersonSummary[][],
  descendantGens: PersonSummary[][],
  fetchedPersons: Map<number, Person>,
): Layout {
  // Top → bottom: oldest ancestors, ..., parents, root, children, ..., youngest descendants
  const rows: PersonSummary[][] = [...[...ancestorGens].reverse(), [root], ...descendantGens];

  let maxCols = 1;
  const positions: Record<number, NodePos> = {};
  rows.forEach((row, rIdx) => {
    maxCols = Math.max(maxCols, row.length);
    const totalW = (row.length - 1) * COL_W;
    row.forEach((p, cIdx) => {
      positions[p.id] = { x: cIdx * COL_W - totalW / 2, y: rIdx * ROW_H };
    });
  });

  const allNodes = rows.flat();
  const visibleIds = new Set(allNodes.map((p) => p.id));

  const seen = new Set<string>();
  const links: GraphLink[] = [];
  for (const p of fetchedPersons.values()) {
    for (const s of p.godChildren) {
      const key = `${s.godFatherId}-${s.godChildId}`;
      if (visibleIds.has(s.godFatherId) && visibleIds.has(s.godChildId) && !seen.has(key)) {
        seen.add(key);
        links.push({ godFatherId: s.godFatherId, godChildId: s.godChildId });
      }
    }
  }

  const canvasWidth = maxCols * COL_W + 80;
  const canvasHeight = rows.length * ROW_H + 60;
  return { allNodes, positions, links, canvasWidth, canvasHeight, rowCount: rows.length };
}

// ── Sub-components ────────────────────────────────────────────────────────────

function DepthControl({
  label,
  value,
  canShrink,
  canExpand,
  onMinus,
  onPlus,
}: {
  label: string;
  value: number;
  canShrink: boolean;
  canExpand: boolean;
  onMinus: () => void;
  onPlus: () => void;
}) {
  const stop = (fn: () => void) => (e: MouseEvent) => {
    e.stopPropagation();
    fn();
  };
  return (
    <div className="flex items-center gap-0.5 rounded-full border border-line bg-surface px-1.5 py-0.5">
      <span className="px-1 text-[10.5px] text-ink-3">{label}</span>
      <button
        onClick={stop(onMinus)}
        disabled={!canShrink}
        className="flex h-5 w-5 cursor-pointer items-center justify-center rounded-full text-[12px] font-semibold text-ink-2 transition-colors disabled:cursor-default disabled:opacity-30 hover:enabled:text-ink"
      >
        −
      </button>
      <span className="min-w-[14px] text-center text-[11.5px] font-semibold text-ink">{value}</span>
      <button
        onClick={stop(onPlus)}
        disabled={!canExpand}
        className="flex h-5 w-5 cursor-pointer items-center justify-center rounded-full text-[12px] font-semibold text-ink-2 transition-colors disabled:cursor-default disabled:opacity-30 hover:enabled:text-ink"
      >
        +
      </button>
    </div>
  );
}

function ZoomControls({
  onPlus,
  onMinus,
  onReset,
}: {
  onPlus: () => void;
  onMinus: () => void;
  onReset: () => void;
}) {
  return (
    <div className="flex flex-col gap-1.5">
      {(
        [
          ['＋', onPlus],
          ['−', onMinus],
          ['⤓', onReset],
        ] as const
      ).map(([sym, action]) => (
        <button
          key={sym}
          onClick={(e) => {
            e.stopPropagation();
            action();
          }}
          className="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg border border-line bg-surface text-xs font-semibold text-ink-2 transition-colors hover:border-ink hover:text-ink"
        >
          {sym}
        </button>
      ))}
    </div>
  );
}

interface GraphNodeProps {
  person: PersonSummary;
  isSelf: boolean;
  dim: boolean;
  pos: NodePos;
  canvasWidth: number;
  onClick: () => void;
  onHoverEnter: () => void;
  onHoverLeave: () => void;
}

function GraphNode({
  person,
  isSelf,
  dim,
  pos,
  canvasWidth,
  onClick,
  onHoverEnter,
  onHoverLeave,
}: GraphNodeProps) {
  const d = isSelf ? NODE_D_SELF : NODE_D;
  const color = promoColor(person.startYear);
  const offset = isSelf ? -(NODE_D_SELF - NODE_D) / 2 : 0;

  return (
    <div
      data-fg-node
      onMouseEnter={onHoverEnter}
      onMouseLeave={onHoverLeave}
      onClick={onClick}
      style={{
        position: 'absolute',
        left: pos.x + canvasWidth / 2 - NODE_D / 2 + offset,
        top: pos.y + offset,
        width: d,
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        gap: 4,
        cursor: isSelf ? 'default' : 'pointer',
        opacity: dim ? 0.3 : 1,
        transition: 'opacity 0.15s',
      }}
    >
      <div
        style={{
          width: d,
          height: d,
          borderRadius: '50%',
          overflow: 'hidden',
          flexShrink: 0,
          boxShadow: isSelf
            ? `0 0 0 3px ${color}, 0 0 0 6px ${color}25, 0 8px 18px ${color}30`
            : `0 0 0 2px ${color}, 0 2px 6px rgba(0,0,0,0.06)`,
        }}
      >
        <Avatar person={person} fill />
      </div>
      <div
        style={{
          fontSize: 10.5,
          fontWeight: isSelf ? 600 : 500,
          color: isSelf ? 'var(--color-ink)' : 'var(--color-ink-2)',
          textAlign: 'center',
          lineHeight: 1.15,
          maxWidth: 72,
          whiteSpace: 'nowrap',
          overflow: 'hidden',
          textOverflow: 'ellipsis',
          background: 'var(--color-surface)',
          padding: '2px 5px',
          borderRadius: 4,
          border: '1px solid var(--color-line)',
        }}
      >
        {person.firstName}
      </div>
    </div>
  );
}

// ── Main component ────────────────────────────────────────────────────────────

interface FamilyGraphProps {
  person: Person;
}

export function FamilyGraph({ person }: FamilyGraphProps) {
  const navigate = useNavigate();

  const directIds = useMemo(
    () => [
      ...person.godFathers.map((s) => s.godFatherId),
      ...person.godChildren.map((s) => s.godChildId),
    ],
    [person],
  );

  const [fetchedPersons, setFetchedPersons] = useState<Map<number, Person>>(
    () => new Map([[person.id, person]]),
  );
  const [ancestorGens, setAncestorGens] = useState<PersonSummary[][]>([]);
  const [descendantGens, setDescendantGens] = useState<PersonSummary[][]>([]);
  const [initialLoading, setInitialLoading] = useState(() => directIds.length > 0);

  const [pan, setPan] = useState({ x: 0, y: 0 });
  const [zoom, setZoom] = useState(1);
  const [isDragging, setIsDragging] = useState(false);
  const [hoverId, setHoverId] = useState<number | null>(null);
  const dragState = useRef({
    active: false,
    moved: false,
    startX: 0,
    startY: 0,
    originX: 0,
    originY: 0,
  });
  const containerRef = useRef<HTMLDivElement>(null);

  // ── Initial fetch: load direct family members

  useEffect(() => {
    if (directIds.length === 0) return;

    void fetchPersons(directIds).then((fetched) => {
      setFetchedPersons((prev) => new Map([...prev, ...fetched]));

      const parents = person.godFathers
        .map((s) => fetched.get(s.godFatherId))
        .filter((p): p is Person => p !== undefined)
        .map(toSummary);
      const children = person.godChildren
        .map((s) => fetched.get(s.godChildId))
        .filter((p): p is Person => p !== undefined)
        .map(toSummary);

      if (parents.length > 0) setAncestorGens([parents]);
      if (children.length > 0) setDescendantGens([children]);
      setInitialLoading(false);
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [directIds]);

  // ── Layout

  const rootSummary = useMemo(() => toSummary(person), [person]);

  // ── Depth expansion

  const canExpandAncestors = useMemo(() => {
    const frontier =
      ancestorGens.length > 0 ? (ancestorGens[ancestorGens.length - 1] ?? []) : [rootSummary];
    const existingIds = new Set([person.id, ...ancestorGens.flat().map((p) => p.id)]);
    return frontier.some((p) =>
      (fetchedPersons.get(p.id)?.godFathers ?? []).some((s) => !existingIds.has(s.godFatherId)),
    );
  }, [ancestorGens, fetchedPersons, rootSummary, person.id]);

  const canExpandDescendants = useMemo(() => {
    const frontier =
      descendantGens.length > 0 ? (descendantGens[descendantGens.length - 1] ?? []) : [rootSummary];
    const existingIds = new Set([person.id, ...descendantGens.flat().map((p) => p.id)]);
    return frontier.some((p) =>
      (fetchedPersons.get(p.id)?.godChildren ?? []).some((s) => !existingIds.has(s.godChildId)),
    );
  }, [descendantGens, fetchedPersons, rootSummary, person.id]);

  const expandAncestors = () => {
    const frontier =
      ancestorGens.length > 0 ? (ancestorGens[ancestorGens.length - 1] ?? []) : [rootSummary];
    const existingIds = new Set([person.id, ...ancestorGens.flat().map((p) => p.id)]);
    const newIds = [
      ...new Set(
        frontier
          .flatMap((p) => fetchedPersons.get(p.id)?.godFathers.map((s) => s.godFatherId) ?? [])
          .filter((id) => !existingIds.has(id)),
      ),
    ];
    if (newIds.length === 0) return;

    void fetchPersons(newIds.filter((id) => !fetchedPersons.has(id))).then((fetched) => {
      setFetchedPersons((prev) => new Map([...prev, ...fetched]));
      const allFetched = new Map([...fetchedPersons, ...fetched]);
      const newGen = newIds
        .map((id) => allFetched.get(id))
        .filter((p): p is Person => p !== undefined)
        .map(toSummary);
      setAncestorGens((prev) => [...prev, newGen]);
    });
  };

  const expandDescendants = () => {
    const frontier =
      descendantGens.length > 0 ? (descendantGens[descendantGens.length - 1] ?? []) : [rootSummary];
    const existingIds = new Set([person.id, ...descendantGens.flat().map((p) => p.id)]);
    const newIds = [
      ...new Set(
        frontier
          .flatMap((p) => fetchedPersons.get(p.id)?.godChildren.map((s) => s.godChildId) ?? [])
          .filter((id) => !existingIds.has(id)),
      ),
    ];
    if (newIds.length === 0) return;

    void fetchPersons(newIds.filter((id) => !fetchedPersons.has(id))).then((fetched) => {
      setFetchedPersons((prev) => new Map([...prev, ...fetched]));
      const allFetched = new Map([...fetchedPersons, ...fetched]);
      const newGen = newIds
        .map((id) => allFetched.get(id))
        .filter((p): p is Person => p !== undefined)
        .map(toSummary);
      setDescendantGens((prev) => [...prev, newGen]);
    });
  };

  const layout = useMemo(
    () => computeLayout(rootSummary, ancestorGens, descendantGens, fetchedPersons),
    [rootSummary, ancestorGens, descendantGens, fetchedPersons],
  );

  const containerHeight = Math.max(320, Math.min(580, 80 + layout.rowCount * COL_W));

  // ── Pan / zoom handlers

  const handleMouseDown = (e: MouseEvent) => {
    if ((e.target as HTMLElement).closest('[data-fg-node]')) return;
    dragState.current = {
      active: true,
      moved: false,
      startX: e.clientX,
      startY: e.clientY,
      originX: pan.x,
      originY: pan.y,
    };
  };

  const handleMouseMove = (e: MouseEvent) => {
    if (!dragState.current.active) return;
    dragState.current.moved = true;
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
      setZoom((z) => Math.max(0.4, Math.min(2.5, e.deltaY < 0 ? z * 1.1 : z / 1.1)));
    };
    el.addEventListener('wheel', onWheel, { passive: false });
    return () => {
      el.removeEventListener('wheel', onWheel);
    };
  }, [initialLoading]);

  // ── Render

  if (initialLoading) {
    return <Skeleton className="w-full rounded-xl" style={{ height: 320 }} />;
  }

  return (
    <div
      className="relative w-full overflow-hidden rounded-xl border border-line"
      style={{
        height: containerHeight,
        background: 'var(--color-bg)',
        backgroundImage: 'radial-gradient(circle, var(--color-line) 1px, transparent 1px)',
        backgroundSize: '22px 22px',
        cursor: isDragging ? 'grabbing' : 'grab',
        userSelect: 'none',
      }}
      ref={containerRef}
      onMouseDown={handleMouseDown}
      onMouseMove={handleMouseMove}
      onMouseUp={handleMouseUp}
      onMouseLeave={handleMouseUp}
    >
      {/* Depth controls */}
      <div
        className="absolute left-3 top-3 z-10 flex cursor-default items-center gap-2"
        onMouseDown={(e) => {
          e.stopPropagation();
        }}
      >
        <DepthControl
          label="↑ Amont"
          value={ancestorGens.length}
          canShrink={ancestorGens.length > 0}
          canExpand={canExpandAncestors}
          onMinus={() => {
            setAncestorGens((prev) => prev.slice(0, -1));
          }}
          onPlus={expandAncestors}
        />
        <DepthControl
          label="↓ Aval"
          value={descendantGens.length}
          canShrink={descendantGens.length > 0}
          canExpand={canExpandDescendants}
          onMinus={() => {
            setDescendantGens((prev) => prev.slice(0, -1));
          }}
          onPlus={expandDescendants}
        />
      </div>

      {/* Zoom controls */}
      <div
        className="absolute right-3 top-3 z-10 cursor-default"
        onMouseDown={(e) => {
          e.stopPropagation();
        }}
      >
        <ZoomControls
          onPlus={() => {
            setZoom((z) => Math.min(2.5, z * 1.1));
          }}
          onMinus={() => {
            setZoom((z) => Math.max(0.4, z / 1.1));
          }}
          onReset={() => {
            setZoom(1);
            setPan({ x: 0, y: 0 });
          }}
        />
      </div>

      {/* Status bar */}
      <div
        className="absolute bottom-3 left-3 z-10 flex cursor-default items-center gap-3 rounded-lg border border-line bg-surface px-3 py-1.5 text-[11px] text-ink-3"
        onMouseDown={(e) => {
          e.stopPropagation();
        }}
      >
        <span>
          {layout.allNodes.length} personnes · {layout.links.length} liens
        </span>
        <span className="text-ink-4">{Math.round(zoom * 100)}%</span>
      </div>

      {/* Transformed canvas */}
      <div
        style={{
          position: 'absolute',
          left: '50%',
          top: 30,
          transform: `translate(calc(-50% + ${pan.x}px), ${pan.y}px) scale(${zoom})`,
          transformOrigin: '50% 0',
          width: layout.canvasWidth,
          height: layout.canvasHeight,
        }}
      >
        {/* Edges */}
        <svg
          style={{
            position: 'absolute',
            inset: 0,
            width: '100%',
            height: '100%',
            pointerEvents: 'none',
            overflow: 'visible',
          }}
        >
          {layout.links.map((l, i) => {
            const a = layout.positions[l.godFatherId];
            const b = layout.positions[l.godChildId];
            if (!a || !b) return null;
            const half = layout.canvasWidth / 2;
            const r = NODE_D / 2;
            const ax = a.x + half,
              ay = a.y + r;
            const bx = b.x + half,
              by = b.y + r;
            const cy = (ay + by) / 2;
            const color = promoColor(fetchedPersons.get(l.godFatherId)?.startYear ?? 2020);
            const isHighlighted =
              hoverId !== null && (l.godFatherId === hoverId || l.godChildId === hoverId);
            const dim = hoverId !== null && !isHighlighted;
            return (
              <path
                key={i}
                d={`M${ax},${ay} C${ax},${cy} ${bx},${cy} ${bx},${by}`}
                stroke={color}
                strokeWidth={isHighlighted ? 2.5 : 1.5}
                fill="none"
                opacity={dim ? 0.12 : isHighlighted ? 0.95 : 0.55}
              />
            );
          })}
        </svg>

        {/* Nodes */}
        {layout.allNodes.map((p) => {
          const pos = layout.positions[p.id];
          if (!pos) return null;
          const isSelf = p.id === person.id;
          const dim =
            hoverId !== null && hoverId !== p.id && !isNeighbor(p.id, hoverId, layout.links);
          return (
            <GraphNode
              key={p.id}
              person={p}
              isSelf={isSelf}
              dim={dim}
              pos={pos}
              canvasWidth={layout.canvasWidth}
              onHoverEnter={() => {
                setHoverId(p.id);
              }}
              onHoverLeave={() => {
                setHoverId(null);
              }}
              onClick={() => {
                if (dragState.current.moved || isSelf) return;
                void navigate(`/person/${p.id}`);
              }}
            />
          );
        })}
      </div>
    </div>
  );
}
