import { describe, expect, it } from 'vitest';
import { link, mkPerson } from '../../test/factories';
import { computeLayout, shortestPath, toSummary } from './familyGraphLayout';
import type { Person } from '../../types/person';

describe('familyGraphLayout', () => {
  it('conserve type, date et description des liens au-delà du premier niveau', () => {
    const henri = mkPerson(1, 'Henri', 'Durand', 2019);
    const lilian = mkPerson(2, 'Lilian', 'Baudry', 2020);
    const luka = mkPerson(3, 'Luka', 'Maret', 2021);
    link(10, henri, luka);
    link(11, lilian, luka);
    const deep = link(12, henri, lilian, { type: 'HEART', description: 'Soirée' });

    const fetched = new Map<number, Person>([henri, lilian, luka].map((p) => [p.id, p]));
    // Dès le premier niveau, le lien entre les deux parrains (même rangée) est visible
    const layout = computeLayout(toSummary(luka), [[henri, lilian].map(toSummary)], [], fetched);

    const found = layout.links.find((l) => l.id === 12);
    expect(found).toEqual(deep);
  });

  it('place les fillots sous leur parrain (ordre barycentrique)', () => {
    const root = mkPerson(1, 'Root', 'R', 2019);
    const a = mkPerson(2, 'Anna', 'Z', 2020);
    const b = mkPerson(3, 'Bob', 'A', 2020);
    // Fillots nommés pour que l'ordre alphabétique soit l'inverse de l'ordre attendu
    const childOfA = mkPerson(4, 'Aa', 'Zz', 2021);
    const childOfB = mkPerson(5, 'Bb', 'Aa', 2021);
    link(1, root, a);
    link(2, root, b);
    link(3, a, childOfA);
    link(4, b, childOfB);
    const fetched = new Map<number, Person>([root, a, b, childOfA, childOfB].map((p) => [p.id, p]));

    const layout = computeLayout(
      toSummary(root),
      [],
      [[a, b].map(toSummary), [childOfB, childOfA].map(toSummary)],
      fetched,
    );
    const x = (id: number) => layout.positions[id]?.x ?? NaN;
    expect(Math.sign(x(childOfA.id) - x(childOfB.id))).toBe(Math.sign(x(a.id) - x(b.id)));
  });

  it('calcule un cadre qui contient tous les nœuds', () => {
    const root = mkPerson(1, 'Root', 'R', 2019);
    const kids = [2, 3, 4].map((id) => mkPerson(id, `K${String(id)}`, 'K', 2020));
    kids.forEach((k, i) => link(i + 1, root, k));
    const fetched = new Map<number, Person>([root, ...kids].map((p) => [p.id, p]));
    const layout = computeLayout(toSummary(root), [], [kids.map(toSummary)], fetched);
    for (const pos of Object.values(layout.positions)) {
      expect(pos.x).toBeGreaterThan(layout.bounds.x);
      expect(pos.x).toBeLessThan(layout.bounds.x + layout.bounds.width);
      expect(pos.y).toBeGreaterThan(layout.bounds.y);
      expect(pos.y).toBeLessThan(layout.bounds.y + layout.bounds.height);
    }
  });

  it('trouve le plus court chemin non orienté', () => {
    const links = [
      { id: 1, godFatherId: 1, godChildId: 2 },
      { id: 2, godFatherId: 2, godChildId: 3 },
      { id: 3, godFatherId: 1, godChildId: 3 },
    ];
    expect(shortestPath(3, 1, links)?.linkIds).toEqual(new Set([3]));
    expect(shortestPath(1, 4, links)).toBeNull();
  });
});
