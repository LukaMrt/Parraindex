import { useMemo, useState } from 'react';
import { filterPersons } from '../lib/persons';
import type { Person } from '../types/person';

export interface PersonFilterState {
  name: string;
  years: number[];
  alphabetical: boolean;
  filieres: string[];
  schools: string[];
  filtered: Person[];
  setName: (name: string) => void;
  toggleYear: (year: number) => void;
  clearYears: () => void;
  toggleAlphabetical: () => void;
  toggleFiliere: (filiere: string) => void;
  clearFilieres: () => void;
  toggleSchool: (school: string) => void;
  clearSchools: () => void;
}

export function usePersonFilter(persons: Person[], initialYears: number[] = []): PersonFilterState {
  const [name, setName] = useState('');
  const [years, setYears] = useState<number[]>(initialYears);
  const [alphabetical, setAlphabetical] = useState(false);
  const [filieres, setFilieres] = useState<string[]>([]);
  const [schools, setSchools] = useState<string[]>([]);

  const filtered = useMemo(
    () => filterPersons(persons, { name, years, alphabetical, filieres, schools }),
    [persons, name, years, alphabetical, filieres, schools],
  );

  function toggleYear(year: number) {
    setYears((prev) => (prev.includes(year) ? prev.filter((y) => y !== year) : [...prev, year]));
  }

  function toggleFiliere(filiere: string) {
    setFilieres((prev) =>
      prev.includes(filiere) ? prev.filter((f) => f !== filiere) : [...prev, filiere],
    );
  }

  function toggleSchool(school: string) {
    setSchools((prev) =>
      prev.includes(school) ? prev.filter((s) => s !== school) : [...prev, school],
    );
  }

  return {
    name,
    years,
    alphabetical,
    filieres,
    schools,
    filtered,
    setName,
    toggleYear,
    clearYears: () => {
      setYears([]);
    },
    toggleAlphabetical: () => {
      setAlphabetical((prev) => !prev);
    },
    toggleFiliere,
    clearFilieres: () => {
      setFilieres([]);
    },
    toggleSchool,
    clearSchools: () => {
      setSchools([]);
    },
  };
}
