import { useEffect, useState } from 'react';
import { useQueryClient } from '@tanstack/react-query';
import { getTreePage } from '../../lib/api/tree';
import { throwable } from '../../lib/queries';
import type { Person } from '../../types/person';

const PAGE_SIZE = 50;
const QUERY_KEY = ['tree'] as const;

interface PersonsState {
  persons: Person[];
  total: number | null;
  loading: boolean;
  loadingMore: boolean;
}

export function usePersons(): PersonsState {
  const queryClient = useQueryClient();
  const [persons, setPersons] = useState<Person[]>(
    () => queryClient.getQueryData<Person[]>(QUERY_KEY) ?? [],
  );
  const [loading, setLoading] = useState(() => !queryClient.getQueryData<Person[]>(QUERY_KEY));
  const [loadingMore, setLoadingMore] = useState(false);

  useEffect(() => {
    if (queryClient.getQueryData<Person[]>(QUERY_KEY)) return;

    const ac = new AbortController();

    async function load() {
      const first = throwable(await getTreePage(1, PAGE_SIZE));
      if (ac.signal.aborted) return;

      setPersons(first.items);
      setLoading(false);

      const pages = Math.ceil(first.total / PAGE_SIZE);
      if (pages <= 1) {
        queryClient.setQueryData(QUERY_KEY, first.items);
        return;
      }

      setLoadingMore(true);
      const all = [...first.items];

      for (let i = 2; i <= pages; i++) {
        const page = throwable(await getTreePage(i, PAGE_SIZE));
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
        if (ac.signal.aborted) return;
        all.push(...page.items);
        setPersons([...all]);
      }

      setLoadingMore(false);
      queryClient.setQueryData(QUERY_KEY, all);
    }

    load().catch(console.error);
    return () => {
      ac.abort();
    };
  }, [queryClient]);

  return { persons, total: persons.length, loading, loadingMore };
}
