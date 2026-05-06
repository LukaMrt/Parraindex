import { describe, it, expect } from 'vitest';
import { renderHook, act } from '@testing-library/react';
import { usePersonFilter } from './usePersonFilter';
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
  };
}

const people: PersonSummary[] = [
  mkPerson(1, 'Alice', 'Dupont', 2020),
  mkPerson(2, 'Bob', 'Martin', 2021),
  mkPerson(3, 'Charles', 'Bernard', 2022),
];

describe('usePersonFilter', () => {
  it('initialise avec tous les éléments non filtrés', () => {
    const { result } = renderHook(() => usePersonFilter(people));
    expect(result.current.filtered).toHaveLength(3);
    expect(result.current.name).toBe('');
    expect(result.current.years).toEqual([]);
    expect(result.current.alphabetical).toBe(false);
  });

  it('setName filtre par nom', () => {
    const { result } = renderHook(() => usePersonFilter(people));
    act(() => {
      result.current.setName('alice');
    });
    expect(result.current.filtered).toHaveLength(1);
    expect(result.current.filtered[0]?.id).toBe(1);
    expect(result.current.name).toBe('alice');
  });

  it('toggleYear filtre par année', () => {
    const { result } = renderHook(() => usePersonFilter(people));
    act(() => {
      result.current.toggleYear(2021);
    });
    expect(result.current.filtered).toHaveLength(1);
    expect(result.current.filtered[0]?.id).toBe(2);
    expect(result.current.years).toEqual([2021]);
  });

  it('toggleYear sur la même année désélectionne', () => {
    const { result } = renderHook(() => usePersonFilter(people));
    act(() => {
      result.current.toggleYear(2021);
    });
    act(() => {
      result.current.toggleYear(2021);
    });
    expect(result.current.filtered).toHaveLength(3);
    expect(result.current.years).toEqual([]);
  });

  it('multi-sélection de plusieurs années', () => {
    const { result } = renderHook(() => usePersonFilter(people));
    act(() => {
      result.current.toggleYear(2020);
    });
    act(() => {
      result.current.toggleYear(2022);
    });
    expect(result.current.filtered).toHaveLength(2);
    expect(result.current.years).toEqual([2020, 2022]);
  });

  it('clearYears supprime tous les filtres année', () => {
    const { result } = renderHook(() => usePersonFilter(people));
    act(() => {
      result.current.toggleYear(2021);
    });
    act(() => {
      result.current.clearYears();
    });
    expect(result.current.filtered).toHaveLength(3);
    expect(result.current.years).toEqual([]);
  });

  it('toggleAlphabetical bascule le tri', () => {
    const { result } = renderHook(() => usePersonFilter(people));
    expect(result.current.alphabetical).toBe(false);
    act(() => {
      result.current.toggleAlphabetical();
    });
    expect(result.current.alphabetical).toBe(true);
    const lastNames = result.current.filtered.map((p) => p.lastName);
    expect(lastNames).toEqual(['Bernard', 'Dupont', 'Martin']);
  });

  it("toggleAlphabetical deux fois remet dans l'ordre original", () => {
    const { result } = renderHook(() => usePersonFilter(people));
    act(() => {
      result.current.toggleAlphabetical();
    });
    act(() => {
      result.current.toggleAlphabetical();
    });
    expect(result.current.alphabetical).toBe(false);
  });

  it('réagit aux changements de la liste source', () => {
    const { result, rerender } = renderHook(({ persons }) => usePersonFilter(persons), {
      initialProps: { persons: people },
    });
    expect(result.current.filtered).toHaveLength(3);
    const newPeople = [...people, mkPerson(4, 'Diana', 'Moreau', 2023)];
    rerender({ persons: newPeople });
    expect(result.current.filtered).toHaveLength(4);
  });
});
