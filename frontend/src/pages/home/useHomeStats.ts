import { useQuery } from '@tanstack/react-query';
import { homeQueries } from '../../lib/queries';
import { promoColor } from '../../lib/colors';
import type { PromoGroup } from './types';

interface HomeStats {
  totalPersons: number;
  totalPromos: number;
  promoGroups: PromoGroup[];
  loading: boolean;
}

export function useHomeStats(): HomeStats {
  const { data, isLoading } = useQuery(homeQueries.stats());

  const promoGroups: PromoGroup[] = (data?.promoGroups ?? []).map(({ startYear, count }) => ({
    year: startYear,
    color: promoColor(startYear),
    count,
  }));

  return {
    totalPersons: data?.totalPersons ?? 0,
    totalPromos: data?.totalPromos ?? 0,
    promoGroups,
    loading: isLoading,
  };
}
