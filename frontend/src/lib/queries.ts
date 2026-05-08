import type { Result } from '../types/api';
import { getAdminContacts } from './api/admin';
import { getHomeStats } from './api/home';
import { getAccount, getPerson, getPersons } from './api/persons';
import { getSponsor } from './api/sponsors';
import { getTreePage } from './api/tree';

const PAGE_SIZE = 20;

/** Converts a Result<T> into a resolved T or a thrown Error. */
export function throwable<T>(result: Result<T>): T {
  if (!result.ok) throw new Error(result.error.message);
  return result.data;
}

async function fetchAllTreePages() {
  const first = throwable(await getTreePage(1, PAGE_SIZE));
  const pages = Math.ceil(first.total / PAGE_SIZE);
  if (pages <= 1) return first.items;

  const rest = await Promise.all(
    Array.from({ length: pages - 1 }, (_, i) => getTreePage(i + 2, PAGE_SIZE).then(throwable)),
  );
  return [first.items, ...rest.map((r) => r.items)].flat();
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
  tree: () => ({
    queryKey: ['tree'] as const,
    queryFn: fetchAllTreePages,
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

export const adminQueries = {
  contacts: () => ({
    queryKey: ['admin', 'contacts'] as const,
    queryFn: () => getAdminContacts().then(throwable),
    staleTime: 0,
  }),
};
