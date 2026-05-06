import { useEffect, useState } from 'react';
import { getTreePage } from '../../lib/api/tree';
import type { PersonSummary } from '../../types/person';

const PAGE_SIZE = 20;

interface PersonsState {
  persons: PersonSummary[];
  total: number | null;
  loading: boolean;
  loadingMore: boolean;
}

export function usePersons(): PersonsState {
  const [persons, setPersons] = useState<PersonSummary[]>([]);
  const [total, setTotal] = useState<number | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const cancel = { current: false };

    async function loadAll() {
      const first = await getTreePage(1, PAGE_SIZE);
      if (cancel.current) return;

      if (!first.ok) {
        setLoading(false);
        return;
      }

      setPersons(first.data.items);
      setTotal(first.data.total);
      setLoading(false);

      const pages = Math.ceil(first.data.total / PAGE_SIZE);
      for (let page = 2; page <= pages; page++) {
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
        if (cancel.current) break;
        const result = await getTreePage(page, PAGE_SIZE);
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
        if (cancel.current) break;
        if (result.ok) {
          setPersons((prev) => [...prev, ...result.data.items]);
        }
      }
    }

    void loadAll();
    return () => {
      cancel.current = true;
    };
  }, []);

  const loadingMore = !loading && total !== null && persons.length < total;

  return { persons, total, loading, loadingMore };
}
