import { describe, it, expect } from 'vitest';
import { filterPersons, getYearRange } from './persons';
import type { PersonFilter } from './persons';
import type { PersonSummary } from '../types/person';

function mkPerson(
  id: number,
  firstName: string,
  lastName: string,
  startYear: number,
): PersonSummary {
  return {
    id,
    firstName,
    lastName,
    fullName: `${firstName} ${lastName}`,
    startYear,
    picture: null,
    color: '#000',
  };
}

const people: PersonSummary[] = [
  mkPerson(1, 'Alice', 'Dupont', 2020),
  mkPerson(2, 'Bob', 'Martin', 2021),
  mkPerson(3, 'Élodie', 'Lefebvre', 2020),
  mkPerson(4, 'Charles', 'Bernard', 2022),
];

const base: PersonFilter = { name: '', year: null, alphabetical: false };

describe('filterPersons', () => {
  it('retourne tous les éléments sans filtre', () => {
    expect(filterPersons(people, base)).toHaveLength(4);
  });

  it('filtre par année', () => {
    const result = filterPersons(people, { ...base, year: 2020 });
    expect(result).toHaveLength(2);
    expect(result.map((p) => p.id)).toEqual([1, 3]);
  });

  it('filtre par nom (prénom + nom)', () => {
    const result = filterPersons(people, { ...base, name: 'alice' });
    expect(result).toHaveLength(1);
    expect(result[0]?.id).toBe(1);
  });

  it('filtre par nom inversé (nom + prénom)', () => {
    const result = filterPersons(people, { ...base, name: 'martin bob' });
    expect(result).toHaveLength(1);
    expect(result[0]?.id).toBe(2);
  });

  it('ignore les accents dans la recherche', () => {
    const result = filterPersons(people, { ...base, name: 'elodie' });
    expect(result).toHaveLength(1);
    expect(result[0]?.id).toBe(3);
  });

  it('ignore la casse', () => {
    const result = filterPersons(people, { ...base, name: 'DUPONT' });
    expect(result).toHaveLength(1);
    expect(result[0]?.id).toBe(1);
  });

  it('retourne vide si aucun résultat', () => {
    expect(filterPersons(people, { ...base, name: 'zzz' })).toHaveLength(0);
  });

  it('combine filtre nom et année', () => {
    const result = filterPersons(people, { name: 'alice', year: 2020, alphabetical: false });
    expect(result).toHaveLength(1);
    expect(result[0]?.id).toBe(1);
  });

  it('trie par nom de famille (alphabetical)', () => {
    const result = filterPersons(people, { ...base, alphabetical: true });
    const lastNames = result.map((p) => p.lastName);
    expect(lastNames).toEqual(['Bernard', 'Dupont', 'Lefebvre', 'Martin']);
  });

  it('ne mute pas le tableau original', () => {
    const original = [...people];
    filterPersons(people, { ...base, alphabetical: true });
    expect(people).toEqual(original);
  });

  it('retourne vide pour une liste vide', () => {
    expect(filterPersons([], base)).toHaveLength(0);
  });
});

describe('getYearRange', () => {
  it('retourne null pour une liste vide', () => {
    expect(getYearRange([])).toBeNull();
  });

  it('retourne min=max pour un seul élément', () => {
    const one: PersonSummary[] = [mkPerson(1, 'A', 'B', 2020)];
    expect(getYearRange(one)).toEqual({ min: 2020, max: 2020 });
  });

  it('calcule le bon range', () => {
    expect(getYearRange(people)).toEqual({ min: 2020, max: 2022 });
  });
});
