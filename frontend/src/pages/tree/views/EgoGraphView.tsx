import { useEffect, useId, useMemo, useState } from 'react';
import type { KeyboardEvent } from 'react';
import { usePersonNavigation } from '../../../hooks/usePersonNavigation';
import { transformStyle, usePanZoom } from '../../../hooks/usePanZoom';
import { Skeleton } from '../../../components/ui';
import { PersonGraphNode } from '../../../components/graph/PersonGraphNode';
import { SponsorInfoCard } from '../../../components/graph/SponsorInfoCard';
import { GraphStatusBar, ZoomControls, arrowPoints } from '../../../components/graph/GraphControls';
import { promoColor } from '../../../lib/colors';
import { cn } from '../../../lib/cn';
import { shortestPath } from '../../person/familyGraphLayout';
import type { Person } from '../../../types/person';
import type { Sponsor } from '../../../types/sponsor';
import type { SponsorLink } from '../useSponsorsGraph';

const NODE_D = 52;
const CENTER_D = 68;
const RING_GAP = 150;
const MIN_RADIUS = 150;
/** Espace minimal le long d'un anneau entre deux nœuds (nœud + étiquette) */
const MIN_ARC = NODE_D + 46;
const TAU = Math.PI * 2;

interface EgoNode {
  person: Person;
  depth: number;
  /** Centre du nœud */
  x: number;
  y: number;
}

interface EgoLayout {
  center: EgoNode;
  nodes: EgoNode[];
  edges: (SponsorLink & { isDirect: boolean })[];
  positions: Map<number, EgoNode>;
  rings: number[];
  /** Profondeur maximale atteignable depuis le centre (au-delà, « + » n'apporte rien) */
  maxReachableDepth: number;
  bounds: { x: number; y: number; width: number; height: number };
}

/** Moyenne circulaire d'angles */
function circularMean(angles: number[]): number {
  const s = angles.reduce((acc, a) => acc + Math.sin(a), 0);
  const c = angles.reduce((acc, a) => acc + Math.cos(a), 0);
  return Math.atan2(s, c);
}

function buildEgoLayout(
  focus: Person,
  allPersons: Person[],
  allLinks: SponsorLink[],
  maxDepth: number,
): EgoLayout {
  const personMap = new Map(allPersons.map((p) => [p.id, p]));
  const adjacency = new Map<number, number[]>();
  for (const l of allLinks) {
    if (!personMap.has(l.godFatherId) || !personMap.has(l.godChildId)) continue;
    adjacency.set(l.godFatherId, [...(adjacency.get(l.godFatherId) ?? []), l.godChildId]);
    adjacency.set(l.godChildId, [...(adjacency.get(l.godChildId) ?? []), l.godFatherId]);
  }

  // BFS complet (non orienté) : profondeur de chaque personne depuis le centre
  const depthOf = new Map<number, number>([[focus.id, 0]]);
  const queue = [focus.id];
  // for…of sur un tableau qu'on allonge : l'itérateur voit les éléments ajoutés (file BFS)
  for (const id of queue) {
    const d = depthOf.get(id) ?? 0;
    for (const nid of adjacency.get(id) ?? []) {
      if (depthOf.has(nid)) continue;
      depthOf.set(nid, d + 1);
      queue.push(nid);
    }
  }
  const maxReachableDepth = Math.max(0, ...depthOf.values());

  const byDepth = new Map<number, number[]>();
  for (const [id, d] of depthOf) {
    if (d === 0 || d > maxDepth) continue;
    byDepth.set(d, [...(byDepth.get(d) ?? []), id]);
  }

  const angleOf = new Map<number, number>([[focus.id, -Math.PI / 2]]);
  const positions = new Map<number, EgoNode>();
  positions.set(focus.id, { person: focus, depth: 0, x: 0, y: 0 });
  const rings: number[] = [];

  const parentIds = new Set(
    allLinks.filter((l) => l.godChildId === focus.id).map((l) => l.godFatherId),
  );

  let prevRadius = 0;
  for (let depth = 1; depth <= maxDepth; depth++) {
    const ids = byDepth.get(depth);
    if (!ids || ids.length === 0) break;

    // Le rayon grandit avec le nombre de nœuds pour qu'ils ne se chevauchent jamais
    const radius = Math.max(
      MIN_RADIUS + (depth - 1) * RING_GAP,
      prevRadius + RING_GAP,
      (ids.length * MIN_ARC) / TAU,
    );
    prevRadius = radius;
    rings.push(radius);
    const step = TAU / ids.length;

    let ordered: number[];
    let start: number;
    if (depth === 1) {
      // Parrains en haut, fillots en bas
      const parents = ids.filter((id) => parentIds.has(id));
      const children = ids.filter((id) => !parentIds.has(id));
      ordered = [...parents, ...children];
      start = -Math.PI / 2 - ((Math.max(parents.length, 1) - 1) * step) / 2;
    } else {
      // Chaque nœud se place face à ses voisins de l'anneau précédent
      const keyed = ids.map((id) => {
        const neighborAngles = (adjacency.get(id) ?? [])
          .filter((nid) => depthOf.get(nid) === depth - 1)
          .map((nid) => angleOf.get(nid))
          .filter((a): a is number => a !== undefined);
        const a = neighborAngles.length > 0 ? circularMean(neighborAngles) : 0;
        return { id, a: (a + TAU) % TAU };
      });
      keyed.sort((x, y) => x.a - y.a);
      ordered = keyed.map((k) => k.id);
      start = circularMean(keyed.map((k, i) => k.a - i * step));
    }

    ordered.forEach((id, i) => {
      const person = personMap.get(id);
      if (!person) return;
      const angle = start + i * step;
      angleOf.set(id, angle);
      positions.set(id, {
        person,
        depth,
        x: radius * Math.cos(angle),
        y: radius * Math.sin(angle),
      });
    });
  }

  const edges = allLinks
    .filter((l) => positions.has(l.godFatherId) && positions.has(l.godChildId))
    .map((l) => ({ ...l, isDirect: l.godFatherId === focus.id || l.godChildId === focus.id }));

  const extent = (rings[rings.length - 1] ?? 0) + NODE_D + 40;
  const center = positions.get(focus.id) ?? { person: focus, depth: 0, x: 0, y: 0 };
  return {
    center,
    nodes: [...positions.values()].filter((n) => n.depth > 0),
    edges,
    positions,
    rings,
    maxReachableDepth,
    bounds: { x: -extent, y: -extent, width: extent * 2, height: extent * 2 },
  };
}

// ── Recherche (combobox accessible au clavier) ─────────────────────────────────

function PersonSearch({
  persons,
  placeholder,
  autoFocus = false,
  className,
  onSelect,
}: {
  persons: Person[];
  placeholder: string;
  autoFocus?: boolean;
  className?: string;
  onSelect: (id: number) => void;
}) {
  const listId = useId();
  const [search, setSearch] = useState('');
  const [active, setActive] = useState(0);

  const suggestions = useMemo(() => {
    const q = search.trim().toLowerCase().normalize('NFD').replace(/\p{M}/gu, '');
    if (!q) return [];
    return persons
      .filter((p) => p.fullName.toLowerCase().normalize('NFD').replace(/\p{M}/gu, '').includes(q))
      .slice(0, 8);
  }, [search, persons]);

  const select = (id: number) => {
    setSearch('');
    setActive(0);
    onSelect(id);
  };

  const onKeyDown = (e: KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      setActive((i) => Math.min(i + 1, suggestions.length - 1));
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      setActive((i) => Math.max(i - 1, 0));
    } else if (e.key === 'Enter') {
      const p = suggestions[active];
      if (p) select(p.id);
    } else if (e.key === 'Escape') {
      setSearch('');
    }
  };

  const open = suggestions.length > 0;
  return (
    <div className={cn('relative', className)}>
      <input
        role="combobox"
        aria-expanded={open}
        aria-controls={listId}
        aria-autocomplete="list"
        aria-activedescendant={open ? `${listId}-${String(active)}` : undefined}
        aria-label="Rechercher un étudiant"
        autoFocus={autoFocus}
        value={search}
        onChange={(e) => {
          setSearch(e.target.value);
          setActive(0);
        }}
        onKeyDown={onKeyDown}
        placeholder={placeholder}
        className="h-9 w-full rounded-lg border border-line bg-surface px-3 text-[13px] text-ink placeholder:text-ink-3 focus:border-ink focus:outline-none"
      />
      {open && (
        <ul
          id={listId}
          role="listbox"
          className="absolute left-0 right-0 top-full z-20 mt-1 min-w-64 overflow-hidden rounded-lg border border-line bg-surface shadow-lg"
        >
          {suggestions.map((p, i) => (
            <li
              key={p.id}
              id={`${listId}-${String(i)}`}
              role="option"
              aria-selected={i === active}
              onMouseEnter={() => {
                setActive(i);
              }}
              onMouseDown={(e) => {
                e.preventDefault();
                select(p.id);
              }}
              className={cn(
                'flex cursor-pointer items-center gap-2.5 px-3 py-2 text-left text-[13px]',
                i === active && 'bg-bg',
              )}
            >
              <span
                className="h-2 w-2 shrink-0 rounded-full"
                style={{ background: promoColor(p.startYear) }}
              />
              <span className="font-medium text-ink">{p.fullName}</span>
              <span className="ml-auto text-ink-4">{p.startYear}</span>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}

// ── Vue ───────────────────────────────────────────────────────────────────────

interface Props {
  persons: Person[];
  links: SponsorLink[];
  loading: boolean;
  focusId: number | null;
  onFocusChange: (id: number | null) => void;
}

export function EgoGraphView({ persons, links: allLinks, loading, focusId, onFocusChange }: Props) {
  const { navigateTo } = usePersonNavigation();

  const [maxDepth, setMaxDepth] = useState(2);
  const [hoverId, setHoverId] = useState<number | null>(null);
  const [selectedSponsor, setSelectedSponsor] = useState<Sponsor | null>(null);
  const [hoveredLinkId, setHoveredLinkId] = useState<number | null>(null);
  const [navigatingId, setNavigatingId] = useState<number | null>(null);

  const { transform, zoom, isDragging, didDrag, containerRef, zoomBy, fitTo } = usePanZoom({
    minZoom: 0.15,
  });

  const focusPerson = useMemo(
    () => persons.find((p) => p.id === focusId) ?? null,
    [persons, focusId],
  );

  const layout = useMemo(
    () => (focusPerson ? buildEgoLayout(focusPerson, persons, allLinks, maxDepth) : null),
    [focusPerson, persons, allLinks, maxDepth],
  );

  const path = useMemo(
    () =>
      layout && hoverId !== null && hoverId !== layout.center.person.id
        ? shortestPath(hoverId, layout.center.person.id, layout.edges)
        : null,
    [layout, hoverId],
  );

  const bounds = layout?.bounds;
  useEffect(() => {
    if (bounds) fitTo(bounds, { maxZoom: 1 });
  }, [bounds, fitTo]);

  const changeFocus = (id: number | null) => {
    setSelectedSponsor(null);
    setHoverId(null);
    onFocusChange(id);
  };

  if (loading) {
    return (
      <div
        className="flex w-full flex-col gap-3 rounded-xl border border-line bg-surface p-6"
        style={{ minHeight: 520 }}
      >
        <Skeleton className="h-9 w-64 rounded-lg" />
        <Skeleton className="flex-1 rounded-lg" style={{ minHeight: 440 }} />
      </div>
    );
  }

  if (!focusPerson || !layout) {
    return (
      <div
        className="flex w-full flex-col items-center justify-center gap-6 rounded-xl border border-line bg-surface px-4"
        style={{ minHeight: 520 }}
      >
        <div className="flex flex-col items-center gap-2 text-center">
          <svg
            width={40}
            height={40}
            viewBox="0 0 24 24"
            fill="none"
            stroke="var(--color-ink-3)"
            strokeWidth={1.5}
            strokeLinecap="round"
            strokeLinejoin="round"
            aria-hidden
          >
            <circle cx={12} cy={8} r={4} />
            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
            <path d="M18 14l2 2 4-4" />
          </svg>
          <p className="text-[15px] font-medium text-ink">Choisir une personne</p>
          <p className="text-[13px] text-ink-3">
            Recherche un étudiant pour explorer son réseau de parrainage
          </p>
        </div>
        <PersonSearch
          persons={persons}
          placeholder="Rechercher un étudiant…"
          autoFocus
          className="w-full max-w-72"
          onSelect={changeFocus}
        />
      </div>
    );
  }

  const center = layout.center;
  const canGoDeeper = maxDepth < layout.maxReachableDepth;

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
      {/* Recherche / profondeur / fiche (haut-gauche) */}
      <div className="absolute left-3 top-3 z-10 flex flex-wrap items-center gap-2 pr-14">
        <PersonSearch
          persons={persons}
          placeholder={`Centré sur ${focusPerson.fullName}`}
          className="w-56"
          onSelect={changeFocus}
        />

        <div className="flex items-center gap-1 rounded-lg border border-line bg-surface px-2 py-1">
          <span className="text-[11px] text-ink-3">Degrés</span>
          <button
            type="button"
            aria-label="Retirer un degré"
            className="flex h-6 w-6 cursor-pointer items-center justify-center rounded text-[13px] font-semibold text-ink-2 transition-colors disabled:cursor-default disabled:opacity-30 hover:enabled:text-ink"
            disabled={maxDepth <= 1}
            onClick={() => {
              setMaxDepth((d) => Math.max(1, d - 1));
            }}
          >
            −
          </button>
          <span
            className="min-w-[16px] text-center text-[12px] font-semibold text-ink"
            aria-live="polite"
          >
            {maxDepth}
          </span>
          <button
            type="button"
            aria-label="Ajouter un degré"
            title={canGoDeeper ? undefined : 'Tout le réseau est déjà affiché'}
            className="flex h-6 w-6 cursor-pointer items-center justify-center rounded text-[13px] font-semibold text-ink-2 transition-colors disabled:cursor-default disabled:opacity-30 hover:enabled:text-ink"
            disabled={!canGoDeeper}
            onClick={() => {
              setMaxDepth((d) => d + 1);
            }}
          >
            +
          </button>
        </div>

        <button
          type="button"
          onClick={() => {
            setNavigatingId(center.person.id);
            void navigateTo(center.person.id);
          }}
          className="h-8 rounded-lg border border-line bg-surface px-3 text-[12px] font-medium text-ink-2 transition-colors hover:border-ink hover:text-ink"
        >
          {navigatingId === center.person.id ? 'Ouverture…' : 'Voir la fiche →'}
        </button>
      </div>

      <ZoomControls
        zoomBy={zoomBy}
        onFit={() => {
          fitTo(layout.bounds, { maxZoom: 1 });
        }}
      />

      <GraphStatusBar>
        <button
          type="button"
          className="cursor-pointer text-ink-3 underline-offset-2 hover:text-ink hover:underline"
          onClick={() => {
            changeFocus(null);
          }}
        >
          ← Changer
        </button>
        <span>
          {layout.nodes.length + 1} personnes · {layout.edges.length} liens
        </span>
        <span className="hidden text-ink-4 sm:inline">Clic sur une personne : la centrer</span>
        <span className="text-ink-4">{Math.round(zoom * 100)}%</span>
      </GraphStatusBar>

      {/* Canvas transformé (origine = centre du réseau) */}
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
          {layout.rings.map((r) => (
            <circle
              key={r}
              cx={0}
              cy={0}
              r={r}
              fill="none"
              stroke="var(--color-line)"
              strokeWidth={1}
              strokeDasharray="4 6"
              opacity={0.6}
              style={{ pointerEvents: 'none' }}
            />
          ))}

          {layout.edges.map((e) => {
            const from = layout.positions.get(e.godFatherId);
            const to = layout.positions.get(e.godChildId);
            if (!from || !to) return null;
            const isOnPath = path?.linkIds.has(e.id) ?? false;
            const highlight = hoveredLinkId === e.id || isOnPath;
            const dim = path !== null && !isOnPath;
            const color = promoColor(from.person.startYear);
            const dx = to.x - from.x;
            const dy = to.y - from.y;
            const len = Math.hypot(dx, dy) || 1;
            const opacity = dim ? 0.07 : highlight ? 0.95 : e.isDirect ? 0.55 : 0.25;
            return (
              <g key={e.id}>
                <line
                  x1={from.x}
                  y1={from.y}
                  x2={to.x}
                  y2={to.y}
                  stroke={color}
                  strokeWidth={highlight ? 2.5 : e.isDirect ? 1.75 : 1}
                  opacity={opacity}
                  strokeDasharray={e.isDirect ? undefined : '3 4'}
                  style={{ pointerEvents: 'none' }}
                />
                <polygon
                  points={arrowPoints((from.x + to.x) / 2, (from.y + to.y) / 2, dx / len, dy / len)}
                  fill={color}
                  opacity={opacity}
                  style={{ pointerEvents: 'none' }}
                />
                <line
                  x1={from.x}
                  y1={from.y}
                  x2={to.x}
                  y2={to.y}
                  stroke="transparent"
                  strokeWidth={14}
                  style={{ cursor: 'pointer' }}
                  onMouseEnter={() => {
                    setHoveredLinkId(e.id);
                  }}
                  onMouseLeave={() => {
                    setHoveredLinkId(null);
                  }}
                  onClick={() => {
                    if (!didDrag) setSelectedSponsor(e);
                  }}
                >
                  <title>
                    {e.godFatherName} → {e.godChildName}
                  </title>
                </line>
              </g>
            );
          })}
        </svg>

        <PersonGraphNode
          key={`center-${String(center.person.id)}`}
          person={center.person}
          cx={center.x}
          cy={center.y}
          diameter={CENTER_D}
          isSelf
          loading={navigatingId === center.person.id}
          actionLabel={`Voir la fiche de ${center.person.fullName}`}
          onClick={() => {
            if (didDrag) return;
            setNavigatingId(center.person.id);
            void navigateTo(center.person.id);
          }}
        />

        {layout.nodes.map((n) => (
          <PersonGraphNode
            key={n.person.id}
            person={n.person}
            cx={n.x}
            cy={n.y}
            diameter={NODE_D}
            dim={path !== null && !path.nodeIds.has(n.person.id)}
            actionLabel={`Centrer le réseau sur ${n.person.fullName}`}
            onClick={() => {
              if (!didDrag) changeFocus(n.person.id);
            }}
            onHoverChange={(h) => {
              setHoverId(h ? n.person.id : null);
            }}
          />
        ))}
      </div>

      {selectedSponsor && (
        <SponsorInfoCard
          sponsor={selectedSponsor}
          godFatherStartYear={layout.positions.get(selectedSponsor.godFatherId)?.person.startYear}
          onClose={() => {
            setSelectedSponsor(null);
          }}
        />
      )}
    </div>
  );
}
