import { describe, expect, it } from 'vitest';
import { parseDirectoryParams, serializeDirectoryState } from './directoryState';

describe('directoryState', () => {
  it('donne les valeurs par défaut pour une URL vide', () => {
    expect(parseDirectoryParams(new URLSearchParams())).toEqual({
      view: 'grid',
      q: '',
      years: [],
      filieres: [],
      schools: [],
      alphabetical: false,
      focus: null,
    });
  });

  it("reste compatible avec l'ancien lien ?year=2021 de l'accueil", () => {
    expect(parseDirectoryParams(new URLSearchParams('year=2021')).years).toEqual([2021]);
  });

  it('ignore une vue ou un focus invalides', () => {
    const s = parseDirectoryParams(new URLSearchParams('view=nope&focus=abc'));
    expect(s.view).toBe('grid');
    expect(s.focus).toBeNull();
  });

  it('fait un aller-retour sans perte, y compris filières avec virgules', () => {
    const state = {
      view: 'ego' as const,
      q: 'luka',
      years: [2020, 2021],
      filieres: ['Info, parcours A', 'GEA'],
      schools: ['IUT Lyon 1'],
      alphabetical: true,
      focus: 42,
    };
    expect(parseDirectoryParams(serializeDirectoryState(state))).toEqual(state);
  });

  it("n'écrit que ce qui diffère des valeurs par défaut", () => {
    const params = serializeDirectoryState({
      ...parseDirectoryParams(new URLSearchParams()),
      focus: 3,
    });
    // focus n'a de sens qu'en vue Réseau
    expect(params.toString()).toBe('');
  });
});
