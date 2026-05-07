import { useState, type MouseEvent } from 'react';
import { useNavigate } from 'react-router';
import { Skeleton } from '../../components/ui';
import { PersonGraphNode } from '../../components/graph/PersonGraphNode';
import { SponsorInfoCard } from '../../components/graph/SponsorInfoCard';
import { promoColor } from '../../lib/colors';
import type { Person } from '../../types/person';
import type { SponsorSummary } from '../../types/sponsor';
import { NODE_D, NODE_D_SELF, isNeighbor } from './familyGraphLayout';
import type { GraphLink } from './familyGraphLayout';
import { useFamilyGraph } from './useFamilyGraph';

// ── Sub-components ────────────────────────────────────────────────────────────

function DepthControl({
  label,
  value,
  canShrink,
  canExpand,
  loading,
  onMinus,
  onPlus,
}: {
  label: string;
  value: number;
  canShrink: boolean;
  canExpand: boolean;
  loading: boolean;
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
        disabled={!canShrink || loading}
        className="flex h-5 w-5 cursor-pointer items-center justify-center rounded-full text-[12px] font-semibold text-ink-2 transition-colors disabled:cursor-default disabled:opacity-30 hover:enabled:text-ink"
      >
        −
      </button>
      <span className="min-w-[14px] text-center text-[11.5px] font-semibold text-ink">{value}</span>
      <button
        onClick={stop(onPlus)}
        disabled={!canExpand || loading}
        className="flex h-5 w-5 cursor-pointer items-center justify-center rounded-full text-[12px] font-semibold text-ink-2 transition-colors disabled:cursor-default disabled:opacity-30 hover:enabled:text-ink"
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

// ── Main component ────────────────────────────────────────────────────────────

interface FamilyGraphProps {
  person: Person;
}

export function FamilyGraph({ person }: FamilyGraphProps) {
  const navigate = useNavigate();
  const {
    layout,
    containerHeight,
    initialLoading,
    loadingAncestors,
    loadingDescendants,
    pan,
    zoom,
    isDragging,
    hoverId,
    ancestorGens,
    descendantGens,
    canExpandAncestors,
    canExpandDescendants,
    containerRef,
    didDrag,
    setHoverId,
    setZoom,
    resetView,
    handleMouseDown,
    handleMouseMove,
    handleMouseUp,
    shrinkAncestors,
    expandAncestors,
    shrinkDescendants,
    expandDescendants,
  } = useFamilyGraph(person);

  const [selectedSponsor, setSelectedSponsor] = useState<SponsorSummary | null>(null);
  const [hoveredLinkId, setHoveredLinkId] = useState<number | null>(null);
  const [navigatingId, setNavigatingId] = useState<number | null>(null);

  const handleLinkClick = (link: GraphLink) => {
    const godFather = layout.allNodes.find((n) => n.id === link.godFatherId);
    const godChild = layout.allNodes.find((n) => n.id === link.godChildId);
    if (!godFather || !godChild) return;
    const found = [...person.godFathers, ...person.godChildren].find((s) => s.id === link.id);
    setSelectedSponsor(
      found ?? {
        id: link.id,
        godFatherId: link.godFatherId,
        godFatherName: godFather.fullName,
        godChildId: link.godChildId,
        godChildName: godChild.fullName,
        type: 'UNKNOWN',
        date: null,
      },
    );
  };

  if (initialLoading) {
    return <Skeleton className="w-full rounded-xl" style={{ height: 410 }} />;
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
          loading={loadingAncestors}
          onMinus={shrinkAncestors}
          onPlus={expandAncestors}
        />
        <DepthControl
          label="↓ Aval"
          value={descendantGens.length}
          canShrink={descendantGens.length > 0}
          canExpand={canExpandDescendants}
          loading={loadingDescendants}
          onMinus={shrinkDescendants}
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
          onReset={resetView}
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
            overflow: 'visible',
          }}
        >
          {layout.links.map((l: GraphLink, i: number) => {
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
            const color = promoColor(
              layout.allNodes.find((n) => n.id === l.godFatherId)?.startYear,
            );
            const isHighlighted =
              hoveredLinkId === l.id ||
              (hoverId !== null && (l.godFatherId === hoverId || l.godChildId === hoverId));
            const dim = hoverId !== null && !isHighlighted;
            const d = `M${ax},${ay} C${ax},${cy} ${bx},${cy} ${bx},${by}`;
            return (
              <g key={i}>
                <path
                  d={d}
                  stroke={color}
                  strokeWidth={isHighlighted ? 2.5 : 1.5}
                  fill="none"
                  opacity={dim ? 0.12 : isHighlighted ? 0.95 : 0.55}
                  style={{ pointerEvents: 'none' }}
                />
                <path
                  d={d}
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
                    handleLinkClick(l);
                  }}
                />
              </g>
            );
          })}
        </svg>

        {/* Nodes */}
        {layout.allNodes.map((p) => {
          const pos = layout.positions[p.id];
          if (!pos) return null;
          const isSelf = p.id === person.id;
          const d = isSelf ? NODE_D_SELF : NODE_D;
          const offset = isSelf ? -(NODE_D_SELF - NODE_D) / 2 : 0;
          const dim =
            hoverId !== null && hoverId !== p.id && !isNeighbor(p.id, hoverId, layout.links);
          return (
            <PersonGraphNode
              key={p.id}
              person={p}
              diameter={d}
              isSelf={isSelf}
              dim={dim}
              loading={navigatingId === p.id}
              dataAttr="data-fg-node"
              style={{
                position: 'absolute',
                left: pos.x + layout.canvasWidth / 2 - NODE_D / 2 + offset,
                top: pos.y + offset,
              }}
              onMouseEnter={() => {
                setHoverId(p.id);
              }}
              onMouseLeave={() => {
                setHoverId(null);
              }}
              onClick={() => {
                if (didDrag || isSelf) return;
                setNavigatingId(p.id);
                void navigate(`/person/${p.id}`);
              }}
            />
          );
        })}
      </div>

      {selectedSponsor && (
        <SponsorInfoCard
          summary={selectedSponsor}
          godFatherStartYear={
            layout.allNodes.find((n) => n.id === selectedSponsor.godFatherId)?.startYear
          }
          onClose={() => {
            setSelectedSponsor(null);
          }}
        />
      )}
    </div>
  );
}
