import type { PersonSummary } from '../types/person';

export interface PersonFilter {
  name: string;
  years: number[];
  alphabetical: boolean;
}

function normalize(s: string): string {
  return s.toLowerCase().normalize('NFD').replace(/\p{M}/gu, '');
}

export function filterPersons(persons: PersonSummary[], filter: PersonFilter): PersonSummary[] {
  const query = normalize(filter.name.trim());
  const yearSet = new Set(filter.years);

  let result = persons.filter((p) => {
    if (yearSet.size > 0 && !yearSet.has(p.startYear)) return false;
    if (query.length > 0) {
      const full = normalize(`${p.lastName} ${p.firstName}`);
      const reversed = normalize(`${p.firstName} ${p.lastName}`);
      if (!full.includes(query) && !reversed.includes(query)) return false;
    }
    return true;
  });

  if (filter.alphabetical) {
    result = [...result].sort((a, b) =>
      a.lastName.localeCompare(b.lastName, 'fr', { sensitivity: 'base' }),
    );
  }

  return result;
}

export function getYearRange(persons: PersonSummary[]): { min: number; max: number } | null {
  if (persons.length === 0) return null;
  let min = persons[0]?.startYear ?? 0;
  let max = min;
  for (const p of persons) {
    if (p.startYear < min) min = p.startYear;
    if (p.startYear > max) max = p.startYear;
  }
  return { min, max };
}
