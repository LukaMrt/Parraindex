import { describe, expect, it } from 'vitest';
import { link, mkPerson } from '../../../test/factories';
import { computeTreeLayout } from './TreeView';

describe('computeTreeLayout', () => {
  const a = mkPerson(1, 'A', 'A', 2019);
  const b = mkPerson(2, 'B', 'B', 2020);
  const c = mkPerson(3, 'C', 'C', 2021);
  const alone = mkPerson(4, 'D', 'D', 2021);
  const links = [link(1, a, b), link(2, b, c)];

  it('« Avec liens » et « Sans liens » partitionnent les personnes affichées', () => {
    // Filtre promo 2019 + 2021 : B est masqué, A → C devient un lien indirect
    const visible = [a, c, alone];
    const withLinks = computeTreeLayout(visible, links, 'connected').nodes.map((p) => p.id);
    const without = computeTreeLayout(visible, links, 'isolated').nodes.map((p) => p.id);
    expect(withLinks.sort()).toEqual([1, 3]);
    expect(without).toEqual([4]);
  });

  it('compte les intermédiaires masqués des liens indirects', () => {
    const layout = computeTreeLayout([a, c], links, 'all');
    expect(layout.links).toHaveLength(0);
    expect(layout.indirectLinks).toEqual([{ godFatherId: 1, godChildId: 3, hops: 2 }]);
  });

  it('garde les objets parrainage complets pour les liens directs', () => {
    const layout = computeTreeLayout([a, b, c], links, 'all');
    expect(layout.links[0]?.type).toBe('CLASSIC');
    expect(layout.links[0]?.date).toBe('2021-09-01');
  });
});
