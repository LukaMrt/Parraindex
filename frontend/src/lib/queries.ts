import type { Result } from '../types/api';
import { getHomeStats } from './api/home';
import { getAccount, getPerson, getPersons } from './api/persons';
import { getSponsor } from './api/sponsors';

/** Converts a Result<T> into a resolved T or a thrown Error. */
export function throwable<T>(result: Result<T>): T {
  if (!result.ok) throw new Error(result.error.message);
  return result.data;
}

export const homeQueries = {
  stats: () => ({
    queryKey: ['home-stats'] as const,
    queryFn: () => getHomeStats().then(throwable),
    staleTime: 5 * 60 * 1000,
  }),
};

export const personQueries = {
  list: () => ({
    queryKey: ['persons'] as const,
    queryFn: () => getPersons().then(throwable),
    staleTime: 5 * 60 * 1000,
  }),
  detail: (id: number) => ({
    queryKey: ['persons', id] as const,
    queryFn: () => getPerson(id).then(throwable),
  }),
  account: (id: number) => ({
    queryKey: ['persons', id, 'account'] as const,
    queryFn: () => getAccount(id).then((r) => (r.ok ? r.data : null)),
    staleTime: 5 * 60 * 1000,
  }),
};

export const sponsorQueries = {
  detail: (id: number) => ({
    queryKey: ['sponsors', id] as const,
    queryFn: () => getSponsor(id).then(throwable),
    staleTime: 2 * 60 * 1000,
  }),
};
