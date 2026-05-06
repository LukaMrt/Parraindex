import { useEffect, useState } from 'react';
import { getPersons } from '../../lib/api/persons';
import { promoColor } from '../../lib/colors';
import type { PersonSummary } from '../../types/person';
import type { PromoGroup } from './types';

interface HomeStats {
  totalPersons: number;
  totalPromos: number;
  promoGroups: PromoGroup[];
  loading: boolean;
}

export function useHomeStats(): HomeStats {
  const [persons, setPersons] = useState<PersonSummary[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    void getPersons().then((result) => {
      if (result.ok) setPersons(result.data);
      setLoading(false);
    });
  }, []);

  const promoMap = new Map<number, number>();
  for (const p of persons) {
    promoMap.set(p.startYear, (promoMap.get(p.startYear) ?? 0) + 1);
  }

  const promoGroups: PromoGroup[] = Array.from(promoMap.entries())
    .map(([year, count]) => ({ year, color: promoColor(year), count }))
    .sort((a, b) => a.year - b.year);

  return {
    totalPersons: persons.length,
    totalPromos: promoGroups.length,
    promoGroups,
    loading,
  };
}
