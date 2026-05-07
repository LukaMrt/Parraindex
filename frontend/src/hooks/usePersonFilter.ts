import { useMemo, useState } from 'react';
import { filterPersons } from '../lib/persons';
import type { PersonSummary } from '../types/person';

export interface PersonFilterState {
  name: string;
  years: number[];
  alphabetical: boolean;
  filtered: PersonSummary[];
  setName: (name: string) => void;
  toggleYear: (year: number) => void;
  clearYears: () => void;
  toggleAlphabetical: () => void;
}

export function usePersonFilter(
  persons: PersonSummary[],
  initialYears: number[] = [],
): PersonFilterState {
  const [name, setName] = useState('');
  const [years, setYears] = useState<number[]>(initialYears);
  const [alphabetical, setAlphabetical] = useState(false);

  const filtered = useMemo(
    () => filterPersons(persons, { name, years, alphabetical }),
    [persons, name, years, alphabetical],
  );

  function toggleYear(year: number) {
    setYears((prev) => (prev.includes(year) ? prev.filter((y) => y !== year) : [...prev, year]));
  }

  return {
    name,
    years,
    alphabetical,
    filtered,
    setName,
    toggleYear,
    clearYears: () => {
      setYears([]);
    },
    toggleAlphabetical: () => {
      setAlphabetical((prev) => !prev);
    },
  };
}
