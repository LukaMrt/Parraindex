import { useMemo, useState } from 'react';
import { filterPersons } from '../lib/persons';
import type { PersonSummary } from '../types/person';

export interface PersonFilterState {
  name: string;
  year: number | null;
  alphabetical: boolean;
  filtered: PersonSummary[];
  setName: (name: string) => void;
  setYear: (year: number | null) => void;
  toggleAlphabetical: () => void;
}

export function usePersonFilter(persons: PersonSummary[]): PersonFilterState {
  const [name, setName] = useState('');
  const [year, setYear] = useState<number | null>(null);
  const [alphabetical, setAlphabetical] = useState(false);

  const filtered = useMemo(
    () => filterPersons(persons, { name, year, alphabetical }),
    [persons, name, year, alphabetical],
  );

  return {
    name,
    year,
    alphabetical,
    filtered,
    setName,
    setYear,
    toggleAlphabetical: () => {
      setAlphabetical((prev) => !prev);
    },
  };
}
