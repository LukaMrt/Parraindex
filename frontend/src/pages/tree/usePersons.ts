import { useCallback, useEffect, useState } from 'react';
import { useQueryClient } from '@tanstack/react-query';
import { getTreePage } from '../../lib/api/tree';
import { throwable } from '../../lib/queries';
import type { Person } from '../../types/person';

const PAGE_SIZE = 50;
export const TREE_QUERY_KEY = ['tree'] as const;

interface PersonsState {
  persons: Person[];
  /** Nombre total annoncé par l'API (null tant que la 1re page n'est pas arrivée) */
  total: number | null;
  /** 1re page en cours de chargement : rien à afficher */
  loading: boolean;
  /** Pages suivantes en cours de chargement */
  loadingMore: boolean;
  error: string | null;
  retry: () => void;
}

/** Mélange de Fisher-Yates (copie) */
function shuffle<T>(items: T[]): T[] {
  const copy = [...items];
  for (let i = copy.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    const a = copy[i];
    const b = copy[j];
    if (a !== undefined && b !== undefined) {
      copy[i] = b;
      copy[j] = a;
    }
  }
  return copy;
}

/**
 * Charge tout l'annuaire : la 1re page s'affiche dès réception, les suivantes partent
 * en parallèle et sont ajoutées en une fois (un seul re-layout).
 *
 * L'ordre aléatoire est fixé au chargement (chaque lot est mélangé puis ajouté à la fin) :
 * rien ne se réordonne sous les yeux de l'utilisateur, et l'ordre est conservé dans le
 * cache pour les retours sur la page.
 */
export function usePersons(): PersonsState {
  const queryClient = useQueryClient();
  const cached = queryClient.getQueryData<Person[]>(TREE_QUERY_KEY);

  const [persons, setPersons] = useState<Person[]>(() => cached ?? []);
  const [total, setTotal] = useState<number | null>(() => cached?.length ?? null);
  const [loading, setLoading] = useState(() => !cached);
  const [loadingMore, setLoadingMore] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [attempt, setAttempt] = useState(0);

  useEffect(() => {
    if (queryClient.getQueryData<Person[]>(TREE_QUERY_KEY)) return;

    let cancelled = false;

    async function load() {
      setError(null);
      setLoading(true);
      const first = throwable(await getTreePage(1, PAGE_SIZE));
      if (cancelled) return;

      let all = shuffle(first.items);
      setPersons(all);
      setTotal(first.total);
      setLoading(false);

      const pages = Math.ceil(first.total / PAGE_SIZE);
      if (pages > 1) {
        setLoadingMore(true);
        const rest = await Promise.all(
          Array.from({ length: pages - 1 }, (_, i) =>
            getTreePage(i + 2, PAGE_SIZE).then(throwable),
          ),
        );
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition -- modifié par le cleanup pendant l'await
        if (cancelled) return;
        const seen = new Set(all.map((p) => p.id));
        const more = rest.flatMap((page) => page.items).filter((p) => !seen.has(p.id));
        all = [...all, ...shuffle(more)];
        setPersons(all);
        setLoadingMore(false);
      }

      queryClient.setQueryData(TREE_QUERY_KEY, all);
    }

    load().catch((e: unknown) => {
      if (cancelled) return;
      setError(e instanceof Error ? e.message : "Impossible de charger l'annuaire.");
      setLoading(false);
      setLoadingMore(false);
    });

    return () => {
      cancelled = true;
    };
  }, [queryClient, attempt]);

  const retry = useCallback(() => {
    setAttempt((n) => n + 1);
  }, []);

  return { persons, total, loading, loadingMore, error, retry };
}
