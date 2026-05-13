import type { Person } from '../../types/person';

export const COL_W = 110;
export const ROW_H = 110;
export const NODE_D = 44;
export const NODE_D_SELF = 56;

export interface GraphLink {
  id: number;
  godFatherId: number;
  godChildId: number;
}

export interface NodePos {
  x: number; // relative to canvas center
  y: number;
}

export interface Layout {
  allNodes: Person[];
  positions: Record<number, NodePos>;
  links: GraphLink[];
  canvasWidth: number;
  canvasHeight: number;
  rowCount: number;
}

export function toSummary(p: Person): Person {
  return {
    id: p.id,
    firstName: p.firstName,
    lastName: p.lastName,
    fullName: p.fullName,
    picture: p.picture,
    startYear: p.startYear,
  };
}

export function isNeighbor(id: number, pivotId: number, links: GraphLink[]): boolean {
  return links.some(
    (l) =>
      (l.godFatherId === pivotId && l.godChildId === id) ||
      (l.godChildId === pivotId && l.godFatherId === id),
  );
}

/** BFS non-directionnel — retourne les IDs de nœuds et les IDs de liens sur le chemin le plus court entre src et dst, ou null si aucun chemin. */
export function shortestPath(
  srcId: number,
  dstId: number,
  links: GraphLink[],
): { nodeIds: Set<number>; linkIds: Set<number> } | null {
  if (srcId === dstId) return { nodeIds: new Set([srcId]), linkIds: new Set() };

  const prev = new Map<number, { nodeId: number; linkId: number }>();
  const queue: number[] = [srcId];
  prev.set(srcId, { nodeId: -1, linkId: -1 });

  while (queue.length > 0) {
    const cur = queue.shift();
    if (cur === undefined) break;
    for (const l of links) {
      const neighbor =
        l.godFatherId === cur ? l.godChildId : l.godChildId === cur ? l.godFatherId : null;
      if (neighbor === null || prev.has(neighbor)) continue;
      prev.set(neighbor, { nodeId: cur, linkId: l.id });
      if (neighbor === dstId) {
        const nodeIds = new Set<number>();
        const linkIds = new Set<number>();
        let n = dstId;
        while (n !== srcId) {
          nodeIds.add(n);
          const p = prev.get(n);
          if (!p) break;
          linkIds.add(p.linkId);
          n = p.nodeId;
        }
        nodeIds.add(srcId);
        return { nodeIds, linkIds };
      }
      queue.push(neighbor);
    }
  }
  return null;
}

export function computeLayout(
  root: Person,
  ancestorGens: Person[][],
  descendantGens: Person[][],
  fetchedPersons: Map<number, Person>,
): Layout {
  // Top → bottom: oldest ancestors, ..., parents, root, children, ..., youngest descendants
  const rows: Person[][] = [...[...ancestorGens].reverse(), [root], ...descendantGens];

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
        links.push({ id: s.id, godFatherId: s.godFatherId, godChildId: s.godChildId });
      }
    }
  }

  const canvasWidth = maxCols * COL_W + 80;
  const canvasHeight = rows.length * ROW_H + 60;
  return { allNodes, positions, links, canvasWidth, canvasHeight, rowCount: rows.length };
}
