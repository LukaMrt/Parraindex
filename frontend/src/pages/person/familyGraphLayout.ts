import type { PersonSummary } from '../../components/graph/PersonGraphNode';
import type { Bounds } from '../../hooks/usePanZoom';
import type { Person } from '../../types/person';
import type { Sponsor } from '../../types/sponsor';

export const COL_W = 110;
export const ROW_H = 110;
export const NODE_D = 44;
export const NODE_D_SELF = 56;

/** Position du centre d'un nœud dans le canvas */
export interface NodePos {
  x: number;
  y: number;
}

export interface Layout {
  allNodes: PersonSummary[];
  positions: Record<number, NodePos>;
  /** Liens affichés, avec toutes leurs infos (type, date, description) */
  links: Sponsor[];
  bounds: Bounds;
  rowCount: number;
}

export function toSummary(p: Person): PersonSummary {
  return {
    id: p.id,
    firstName: p.firstName,
    lastName: p.lastName,
    fullName: p.fullName,
    picture: p.picture,
    startYear: p.startYear,
  };
}

interface LinkLike {
  id: number;
  godFatherId: number;
  godChildId: number;
}

/** BFS non orienté — nœuds et liens du plus court chemin entre src et dst, ou null. */
export function shortestPath(
  srcId: number,
  dstId: number,
  links: LinkLike[],
): { nodeIds: Set<number>; linkIds: Set<number> } | null {
  if (srcId === dstId) return { nodeIds: new Set([srcId]), linkIds: new Set() };

  const adjacency = new Map<number, { nodeId: number; linkId: number }[]>();
  for (const l of links) {
    const a = adjacency.get(l.godFatherId) ?? [];
    a.push({ nodeId: l.godChildId, linkId: l.id });
    adjacency.set(l.godFatherId, a);
    const b = adjacency.get(l.godChildId) ?? [];
    b.push({ nodeId: l.godFatherId, linkId: l.id });
    adjacency.set(l.godChildId, b);
  }

  const prev = new Map<number, { nodeId: number; linkId: number }>([
    [srcId, { nodeId: -1, linkId: -1 }],
  ]);
  const queue = [srcId];
  // for…of sur un tableau qu'on allonge : l'itérateur voit les éléments ajoutés (file BFS)
  for (const cur of queue) {
    for (const next of adjacency.get(cur) ?? []) {
      if (prev.has(next.nodeId)) continue;
      prev.set(next.nodeId, { nodeId: cur, linkId: next.linkId });
      if (next.nodeId === dstId) {
        const nodeIds = new Set<number>([srcId]);
        const linkIds = new Set<number>();
        let n = dstId;
        while (n !== srcId) {
          nodeIds.add(n);
          const p = prev.get(n);
          if (!p) break;
          linkIds.add(p.linkId);
          n = p.nodeId;
        }
        return { nodeIds, linkIds };
      }
      queue.push(next.nodeId);
    }
  }
  return null;
}

const byYearThenName = (a: PersonSummary, b: PersonSummary) =>
  a.startYear - b.startYear || a.lastName.localeCompare(b.lastName, 'fr');

export function computeLayout(
  root: PersonSummary,
  ancestorGens: PersonSummary[][],
  descendantGens: PersonSummary[][],
  fetchedPersons: Map<number, Person>,
): Layout {
  // Un nœud n'apparaît que dans une seule rangée
  const placedIds = new Set<number>();
  const dedup = (gen: PersonSummary[]) =>
    gen.filter((p) => {
      if (placedIds.has(p.id)) return false;
      placedIds.add(p.id);
      return true;
    });

  const rootRowGen = dedup([root]);
  const nonEmpty = (row: PersonSummary[]) => row.length > 0;
  const ancestorRows = ancestorGens.map(dedup).filter(nonEmpty); // [parents, grands-parents, …]
  const descendantRows = descendantGens.map(dedup).filter(nonEmpty); // [fillots, petits-fillots, …]

  // Ordonnancement barycentrique, de la racine vers l'extérieur : chaque nœud se place
  // sous/au-dessus de la moyenne de ses voisins déjà placés → beaucoup moins de croisements.
  const xOf = new Map<number, number>();
  const placeRow = (row: PersonSummary[], neighbors: (id: number) => number[]) => {
    const key = (p: PersonSummary) => {
      const xs = neighbors(p.id)
        .map((id) => xOf.get(id))
        .filter((x): x is number => x !== undefined);
      return xs.length > 0 ? xs.reduce((s, x) => s + x, 0) / xs.length : 0;
    };
    const sorted = [...row]
      .map((p) => ({ p, k: key(p) }))
      .sort((a, b) => a.k - b.k || byYearThenName(a.p, b.p))
      .map(({ p }) => p);
    const totalW = (sorted.length - 1) * COL_W;
    sorted.forEach((p, i) => xOf.set(p.id, i * COL_W - totalW / 2));
    return sorted;
  };

  const childrenIds = (id: number) =>
    (fetchedPersons.get(id)?.godChildren ?? []).map((s) => s.godChildId);
  const fatherIds = (id: number) =>
    (fetchedPersons.get(id)?.godFathers ?? []).map((s) => s.godFatherId);

  placeRow(rootRowGen, () => []);
  const orderedAncestors = ancestorRows.map((row) => placeRow(row, childrenIds));
  const orderedDescendants = descendantRows.map((row) => placeRow(row, fatherIds));

  // Haut → bas : ancêtres les plus lointains, …, parrains, racine, fillots, …
  const rows = [...[...orderedAncestors].reverse(), rootRowGen, ...orderedDescendants];

  const positions: Record<number, NodePos> = {};
  rows.forEach((row, rIdx) => {
    row.forEach((p) => {
      positions[p.id] = { x: xOf.get(p.id) ?? 0, y: rIdx * ROW_H };
    });
  });

  const allNodes = rows.flat();
  const visibleIds = new Set(allNodes.map((p) => p.id));

  // Tous les parrainages entre personnes affichées sont visibles, y compris ceux qui
  // relient deux personnes d'une même rangée ou l'amont à l'aval (diagonales).
  const seen = new Set<number>();
  const links: Sponsor[] = [];
  for (const p of fetchedPersons.values()) {
    for (const s of [...p.godChildren, ...p.godFathers]) {
      if (seen.has(s.id)) continue;
      if (!visibleIds.has(s.godFatherId) || !visibleIds.has(s.godChildId)) continue;
      seen.add(s.id);
      links.push(s);
    }
  }

  // Cadre du contenu : marges pour les étiquettes (bas) et les arches entre voisins (haut)
  const xs = allNodes.map((p) => positions[p.id]?.x ?? 0);
  const minX = Math.min(...xs) - COL_W / 2;
  const maxX = Math.max(...xs) + COL_W / 2;
  const top = -NODE_D_SELF / 2 - 50;
  const bottom = (rows.length - 1) * ROW_H + NODE_D / 2 + 50;

  return {
    allNodes,
    positions,
    links,
    bounds: { x: minX, y: top, width: maxX - minX, height: bottom - top },
    rowCount: rows.length,
  };
}
