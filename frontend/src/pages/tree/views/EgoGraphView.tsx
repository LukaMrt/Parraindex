import { useMemo, useState } from 'react';
import { usePersonNavigation } from '../../../hooks/usePersonNavigation';
import { Skeleton } from '../../../components/ui';
import { PersonGraphNode } from '../../../components/graph/PersonGraphNode';
import { SponsorInfoCard } from '../../../components/graph/SponsorInfoCard';
import { promoColor } from '../../../lib/colors';
import { usePanZoom } from '../../../hooks/usePanZoom';
import type { Person } from '../../../types/person';
import type { Sponsor } from '../../../types/sponsor';
import type { SponsorLink } from '../useSponsorsGraph';

const NODE_D = 52;
const CENTER_D = 68;
const RING_GAP = 160;
const MIN_RADIUS = 160;

interface EgoNode {
  person: Person;
  depth: number;
  angle: number;
  x: number;
  y: number;
}

interface EgoEdge {
  link: SponsorLink;
  from: EgoNode;
  to: EgoNode;
  isDirect: boolean;
}

interface EgoLayout {
  center: EgoNode;
  nodes: EgoNode[];
  edges: EgoEdge[];
  width: number;
  height: number;
}

function buildEgoLayout(
  focus: Person,
  allPersons: Person[],
  allLinks: SponsorLink[],
  maxDepth: number,
): EgoLayout {
  const personMap = new Map(allPersons.map((p) => [p.id, p]));

  // BFS bidirectionnel depuis le nœud central
  const depthOf = new Map<number, number>();
  depthOf.set(focus.id, 0);
  const queue: number[] = [focus.id];

  while (queue.length) {
    const id = queue.shift();
    if (id === undefined) break;
    const d = depthOf.get(id) ?? 0;
    if (d >= maxDepth) continue;

    for (const l of allLinks) {
      let neighborId: number | null = null;
      if (l.godFatherId === id) neighborId = l.godChildId;
      else if (l.godChildId === id) neighborId = l.godFatherId;
      if (neighborId !== null && !depthOf.has(neighborId) && personMap.has(neighborId)) {
        depthOf.set(neighborId, d + 1);
        queue.push(neighborId);
      }
    }
  }

  // Grouper par profondeur
  const byDepth = new Map<number, number[]>();
  for (const [id, d] of depthOf) {
    if (d === 0) continue;
    if (!byDepth.has(d)) byDepth.set(d, []);
    byDepth.get(d)?.push(id);
  }

  // Calculer les positions en layout radial
  const cx = 0;
  const cy = 0;
  const nodesMap = new Map<number, EgoNode>();

  const centerNode: EgoNode = {
    person: focus,
    depth: 0,
    angle: 0,
    x: cx,
    y: cy,
  };
  nodesMap.set(focus.id, centerNode);

  for (const [depth, ids] of byDepth) {
    const radius = MIN_RADIUS + (depth - 1) * RING_GAP;
    ids.forEach((id, i) => {
      const angle = (2 * Math.PI * i) / ids.length - Math.PI / 2;
      const person = personMap.get(id);
      if (!person) return;
      nodesMap.set(id, {
        person,
        depth,
        angle,
        x: cx + radius * Math.cos(angle),
        y: cy + radius * Math.sin(angle),
      });
    });
  }

  // Arêtes visibles uniquement entre nœuds présents
  const edges: EgoEdge[] = [];
  for (const l of allLinks) {
    const from = nodesMap.get(l.godFatherId);
    const to = nodesMap.get(l.godChildId);
    if (!from || !to) continue;
    const isDirect = from.depth === 0 || to.depth === 0;
    edges.push({ link: l, from, to, isDirect });
  }

  const allNodes = [...nodesMap.values()];
  const xs = allNodes.map((n) => n.x);
  const ys = allNodes.map((n) => n.y);
  const pad = 120;
  const minX = Math.min(...xs) - pad;
  const minY = Math.min(...ys) - pad;
  const maxX = Math.max(...xs) + pad + NODE_D;
  const maxY = Math.max(...ys) + pad + NODE_D;

  // Translater pour que les coordonnées soient toutes positives
  const offsetX = -minX;
  const offsetY = -minY;
  for (const n of allNodes) {
    n.x += offsetX;
    n.y += offsetY;
  }

  return {
    center: centerNode,
    nodes: allNodes.filter((n) => n.depth > 0),
    edges,
    width: maxX - minX,
    height: maxY - minY,
  };
}

function findPathToCenter(
  fromId: number,
  edges: EgoEdge[],
  centerId: number,
): { nodeIds: Set<number>; edgeIndices: Set<number> } {
  // BFS depuis fromId vers le centre en remontant les arêtes (dans les deux sens)
  const nodeIds = new Set<number>();
  const edgeIndices = new Set<number>();
  const prev = new Map<number, { nodeId: number; edgeIdx: number }>();
  const visited = new Set<number>([fromId]);
  const queue = [fromId];

  while (queue.length) {
    const cur = queue.shift();
    if (cur === undefined) break;
    if (cur === centerId) break;
    edges.forEach((e, i) => {
      const neighbor =
        e.from.person.id === cur
          ? e.to.person.id
          : e.to.person.id === cur
            ? e.from.person.id
            : null;
      if (neighbor === null || visited.has(neighbor)) return;
      visited.add(neighbor);
      prev.set(neighbor, { nodeId: cur, edgeIdx: i });
      queue.push(neighbor);
    });
  }

  // Remonter depuis le centre jusqu'à fromId
  let cur = centerId;
  while (cur !== fromId) {
    const p = prev.get(cur);
    if (!p) break;
    nodeIds.add(cur);
    edgeIndices.add(p.edgeIdx);
    cur = p.nodeId;
  }
  nodeIds.add(fromId);
  nodeIds.add(centerId);
  return { nodeIds, edgeIndices };
}

interface Props {
  persons: Person[];
  links: SponsorLink[];
  loading: boolean;
}

export function EgoGraphView({ persons, links: allLinks, loading }: Props) {
  const { navigateTo } = usePersonNavigation();

  const [focusId, setFocusId] = useState<number | null>(null);
  const [maxDepth, setMaxDepth] = useState(2);
  const [hoverId, setHoverId] = useState<number | null>(null);
  const [selectedSponsor, setSelectedSponsor] = useState<Sponsor | null>(null);
  const [hoveredLinkIdx, setHoveredLinkIdx] = useState<number | null>(null);
  const [navigatingId, setNavigatingId] = useState<number | null>(null);
  const [search, setSearch] = useState('');

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
  } = usePanZoom({ dragBlockSelector: '[data-ego-node]', minZoom: 0.2 });

  const focusPerson = useMemo(
    () => persons.find((p) => p.id === focusId) ?? null,
    [persons, focusId],
  );

  const layout = useMemo(() => {
    if (!focusPerson) return null;
    return buildEgoLayout(focusPerson, persons, allLinks, maxDepth);
  }, [focusPerson, persons, allLinks, maxDepth]);

  const suggestions = useMemo(() => {
    if (!search.trim()) return [];
    const q = search.toLowerCase();
    return persons.filter((p) => p.fullName.toLowerCase().includes(q)).slice(0, 8);
  }, [search, persons]);

  const handleNodeClick = (id: number) => {
    if (!didDrag) {
      setFocusId(id);
      setSelectedSponsor(null);
      setHoverId(null);
      resetView();
    }
  };

  const handleNavigate = (id: number) => {
    setNavigatingId(id);
    void navigateTo(id);
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

  if (!focusPerson) {
    return (
      <div
        className="flex w-full flex-col items-center justify-center gap-6 rounded-xl border border-line bg-surface"
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

        <div className="relative w-72">
          <input
            autoFocus
            value={search}
            onChange={(e) => {
              setSearch(e.target.value);
            }}
            placeholder="Rechercher un étudiant…"
            className="w-full rounded-lg border border-line bg-surface px-3 py-2 text-[13.5px] text-ink placeholder:text-ink-4 focus:border-ink focus:outline-none"
          />
          {suggestions.length > 0 && (
            <div className="absolute left-0 right-0 top-full z-20 mt-1 overflow-hidden rounded-lg border border-line bg-surface shadow-lg">
              {suggestions.map((p) => (
                <button
                  key={p.id}
                  className="flex w-full cursor-pointer items-center gap-2.5 px-3 py-2 text-left text-[13px] hover:bg-bg"
                  onClick={() => {
                    setFocusId(p.id);
                    setSearch('');
                    resetView();
                  }}
                >
                  <span
                    className="h-2 w-2 shrink-0 rounded-full"
                    style={{ background: promoColor(p.startYear) }}
                  />
                  <span className="font-medium text-ink">{p.fullName}</span>
                  <span className="ml-auto text-ink-4">{p.startYear}</span>
                </button>
              ))}
            </div>
          )}
        </div>
      </div>
    );
  }

  const allLayoutNodes = layout ? [layout.center, ...layout.nodes] : [];

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
      {/* Barre de recherche / changement de focus (haut-gauche) */}
      <div
        className="absolute left-3 top-3 z-10 flex cursor-default items-center gap-2"
        onMouseDown={(e) => {
          e.stopPropagation();
        }}
      >
        <div className="relative">
          <input
            value={search}
            onChange={(e) => {
              setSearch(e.target.value);
            }}
            placeholder={focusPerson.fullName}
            className="h-8 w-52 rounded-lg border border-line bg-surface px-3 text-[12.5px] text-ink placeholder:text-ink-2 focus:border-ink focus:outline-none"
          />
          {suggestions.length > 0 && (
            <div className="absolute left-0 top-full z-20 mt-1 w-64 overflow-hidden rounded-lg border border-line bg-surface shadow-lg">
              {suggestions.map((p) => (
                <button
                  key={p.id}
                  className="flex w-full cursor-pointer items-center gap-2.5 px-3 py-2 text-left text-[12.5px] hover:bg-bg"
                  onClick={() => {
                    setFocusId(p.id);
                    setSearch('');
                    setSelectedSponsor(null);
                    resetView();
                  }}
                >
                  <span
                    className="h-2 w-2 shrink-0 rounded-full"
                    style={{ background: promoColor(p.startYear) }}
                  />
                  <span className="font-medium text-ink">{p.fullName}</span>
                  <span className="ml-auto text-ink-4">{p.startYear}</span>
                </button>
              ))}
            </div>
          )}
        </div>

        {/* Profondeur */}
        <div className="flex items-center gap-1 rounded-lg border border-line bg-surface px-2 py-1">
          <span className="text-[11px] text-ink-3">Degrés</span>
          <button
            className="flex h-5 w-5 cursor-pointer items-center justify-center rounded text-[13px] font-semibold text-ink-2 transition-colors disabled:cursor-default disabled:opacity-30 hover:enabled:text-ink"
            disabled={maxDepth <= 1}
            onClick={(e) => {
              e.stopPropagation();
              setMaxDepth((d) => Math.max(1, d - 1));
            }}
          >
            −
          </button>
          <span className="min-w-[16px] text-center text-[12px] font-semibold text-ink">
            {maxDepth}
          </span>
          <button
            className="flex h-5 w-5 cursor-pointer items-center justify-center rounded text-[13px] font-semibold text-ink-2 transition-colors hover:text-ink"
            onClick={(e) => {
              e.stopPropagation();
              setMaxDepth((d) => d + 1);
            }}
          >
            +
          </button>
        </div>
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
                setZoom((z) => Math.max(0.2, z / 1.1));
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
        <button
          className="cursor-pointer text-ink-3 underline-offset-2 hover:text-ink hover:underline"
          onClick={() => {
            setFocusId(null);
            setSearch('');
            setSelectedSponsor(null);
          }}
        >
          ← Changer
        </button>
        <span>
          {allLayoutNodes.length} nœuds · {layout?.edges.length ?? 0} liens
        </span>
        <span className="text-ink-4">{Math.round(zoom * 100)}%</span>
      </div>

      {/* Canvas transformé */}
      {layout && (
        <div
          style={{
            transform: `translate(${pan.x}px, ${pan.y}px) scale(${zoom})`,
            transformOrigin: '0 0',
            width: layout.width,
            height: layout.height,
            position: 'relative',
          }}
        >
          {/* Anneaux de profondeur */}
          {Array.from({ length: maxDepth }, (_, i) => i + 1).map((d) => {
            const r = MIN_RADIUS + (d - 1) * RING_GAP;
            const cx = layout.center.x + CENTER_D / 2;
            const cy = layout.center.y + CENTER_D / 2;
            return (
              <svg
                key={d}
                style={{
                  position: 'absolute',
                  inset: 0,
                  width: '100%',
                  height: '100%',
                  pointerEvents: 'none',
                }}
              >
                <circle
                  cx={cx}
                  cy={cy}
                  r={r}
                  fill="none"
                  stroke="var(--color-line)"
                  strokeWidth={1}
                  strokeDasharray="4 6"
                  opacity={0.5}
                />
              </svg>
            );
          })}

          {/* Arêtes SVG */}
          <svg style={{ position: 'absolute', inset: 0, width: '100%', height: '100%' }}>
            {(() => {
              const path =
                hoverId !== null
                  ? findPathToCenter(hoverId, layout.edges, layout.center.person.id)
                  : null;
              return layout.edges.map((e, i) => {
                const r_from = e.from.depth === 0 ? CENTER_D / 2 : NODE_D / 2;
                const r_to = e.to.depth === 0 ? CENTER_D / 2 : NODE_D / 2;
                const ax = e.from.x + r_from;
                const ay = e.from.y + r_from;
                const bx = e.to.x + r_to;
                const by = e.to.y + r_to;
                const isHovered = hoveredLinkIdx === i;
                const isOnPath = path?.edgeIndices.has(i) ?? false;
                const dim = hoverId !== null && !isOnPath;
                const highlight = isHovered || isOnPath;
                const color = promoColor(e.from.person.startYear);

                return (
                  <g key={i}>
                    <line
                      x1={ax}
                      y1={ay}
                      x2={bx}
                      y2={by}
                      stroke={color}
                      strokeWidth={highlight ? 2.5 : e.isDirect ? 1.75 : 1}
                      opacity={dim ? 0.07 : highlight ? 0.95 : e.isDirect ? 0.55 : 0.25}
                      strokeDasharray={e.isDirect ? undefined : '3 4'}
                      style={{ pointerEvents: 'none' }}
                    />
                    {/* Zone de clic élargie */}
                    <line
                      x1={ax}
                      y1={ay}
                      x2={bx}
                      y2={by}
                      stroke="transparent"
                      strokeWidth={12}
                      style={{ cursor: 'pointer' }}
                      onMouseEnter={() => {
                        setHoveredLinkIdx(i);
                      }}
                      onMouseLeave={() => {
                        setHoveredLinkIdx(null);
                      }}
                      onClick={() => {
                        const gf = persons.find((p) => p.id === e.link.godFatherId);
                        const gc = persons.find((p) => p.id === e.link.godChildId);
                        setSelectedSponsor({
                          id: e.link.id,
                          godFatherId: e.link.godFatherId,
                          godFatherName: gf?.fullName ?? String(e.link.godFatherId),
                          godChildId: e.link.godChildId,
                          godChildName: gc?.fullName ?? String(e.link.godChildId),
                          type: 'UNKNOWN',
                          date: null,
                        });
                      }}
                    />
                    {/* Flèche directionnelle au milieu */}
                    {(() => {
                      const mx = (ax + bx) / 2;
                      const my = (ay + by) / 2;
                      const dx = bx - ax;
                      const dy = by - ay;
                      const len = Math.sqrt(dx * dx + dy * dy) || 1;
                      const ux = dx / len;
                      const uy = dy / len;
                      const s = 5;
                      const p1x = mx + ux * s;
                      const p1y = my + uy * s;
                      const p2x = mx - ux * s - uy * s * 0.6;
                      const p2y = my - uy * s + ux * s * 0.6;
                      const p3x = mx - ux * s + uy * s * 0.6;
                      const p3y = my - uy * s - ux * s * 0.6;
                      return (
                        <polygon
                          points={`${p1x},${p1y} ${p2x},${p2y} ${p3x},${p3y}`}
                          fill={color}
                          opacity={dim ? 0.07 : highlight ? 0.95 : e.isDirect ? 0.5 : 0.2}
                          style={{ pointerEvents: 'none' }}
                        />
                      );
                    })()}
                  </g>
                );
              });
            })()}
          </svg>

          {/* Nœud central */}
          <PersonGraphNode
            key={`center-${layout.center.person.id}`}
            person={layout.center.person}
            diameter={CENTER_D}
            isSelf
            dataAttr="data-ego-node"
            style={{
              position: 'absolute',
              left: layout.center.x,
              top: layout.center.y,
              cursor: 'pointer',
            }}
            onClick={() => {
              handleNavigate(layout.center.person.id);
            }}
            loading={navigatingId === layout.center.person.id}
          />

          {/* Nœuds voisins */}
          {(() => {
            const path =
              hoverId !== null
                ? findPathToCenter(hoverId, layout.edges, layout.center.person.id)
                : null;
            return layout.nodes.map((n) => {
              const dim = path !== null && !path.nodeIds.has(n.person.id);
              return (
                <PersonGraphNode
                  key={n.person.id}
                  person={n.person}
                  diameter={NODE_D}
                  dim={dim}
                  loading={navigatingId === n.person.id}
                  dataAttr="data-ego-node"
                  style={{ position: 'absolute', left: n.x, top: n.y }}
                  onClick={() => {
                    handleNodeClick(n.person.id);
                  }}
                  onMouseEnter={() => {
                    setHoverId(n.person.id);
                  }}
                  onMouseLeave={() => {
                    setHoverId(null);
                  }}
                />
              );
            });
          })()}
        </div>
      )}

      {selectedSponsor && (
        <SponsorInfoCard
          summary={selectedSponsor}
          godFatherStartYear={persons.find((n) => n.id === selectedSponsor.godFatherId)?.startYear}
          position="top-right"
          onClose={() => {
            setSelectedSponsor(null);
          }}
        />
      )}
    </div>
  );
}
