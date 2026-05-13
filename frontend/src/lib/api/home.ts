import { get } from './client';
import type { Result } from '../../types/api';

export interface HomeStatsPromoGroup {
  startYear: number;
  count: number;
}

export interface HomeStats {
  totalPersons: number;
  totalPromos: number;
  promoGroups: HomeStatsPromoGroup[];
}

export function getHomeStats(): Promise<Result<HomeStats>> {
  return get<HomeStats>('/api/home-stats');
}
