import { useQuery } from '@tanstack/react-query';
import { personQueries } from '../../lib/queries';
import { promoColor } from '../../lib/colors';
import type { PromoGroup } from './types';

interface HomeStats {
  totalPersons: number;
  totalPromos: number;
  promoGroups: PromoGroup[];
  loading: boolean;
}

export function useHomeStats(): HomeStats {
  const { data: persons = [], isLoading } = useQuery(personQueries.list());

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
    loading: isLoading,
  };
}
