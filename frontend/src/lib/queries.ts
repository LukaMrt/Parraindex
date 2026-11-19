import type { ErrorCode, Result } from '../types/api';
import { getHomeStats } from './api/home';
import { getAccount, getPerson, searchPersons } from './api/persons';
import { getSponsor } from './api/sponsors';

/** Erreur levée par les query functions : conserve le code API pour l'UI. */
export class ApiQueryError extends Error {
  constructor(
    message: string,
    public readonly code: ErrorCode,
  ) {
    super(message);
    this.name = 'ApiQueryError';
  }
}

export function isNotFound(error: unknown): boolean {
  return error instanceof ApiQueryError && error.code.endsWith('_NOT_FOUND');
}

/** Converts a Result<T> into a resolved T or a thrown ApiQueryError. */
export function throwable<T>(result: Result<T>): T {
  if (!result.ok) throw new ApiQueryError(result.error.message, result.error.code);
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
  search: (q: string, limit = 20) => ({
    queryKey: ['persons', 'search', q, limit] as const,
    queryFn: () => searchPersons({ q, limit }).then(throwable),
    staleTime: 60 * 1000,
    enabled: q.trim().length >= 2,
  }),
  detail: (id: number) => ({
    queryKey: ['persons', id] as const,
    queryFn: () => getPerson(id).then(throwable),
  }),
  /**
   * Version éditable d'une fiche : inclut les caractéristiques privées (l'API ne les
   * renvoie qu'au propriétaire / aux admins). Clé distincte de `detail` pour ne jamais
   * réutiliser une version publique en cache (issue d'un batch d'arbre, par exemple) :
   * à l'enregistrement, les caractéristiques absentes seraient supprimées.
   */
  editable: (id: number) => ({
    queryKey: ['persons', id, 'edit'] as const,
    queryFn: () => getPerson(id).then(throwable),
    staleTime: 0,
    gcTime: 0,
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
