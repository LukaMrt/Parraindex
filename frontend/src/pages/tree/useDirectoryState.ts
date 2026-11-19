import { useCallback, useEffect, useMemo } from 'react';
import { useSearchParams } from 'react-router';
import {
  parseDirectoryParams,
  rememberDirectorySearch,
  serializeDirectoryState,
} from './directoryState';
import type { DirectoryState } from './directoryState';

function toggle<T>(list: T[], item: T): T[] {
  return list.includes(item) ? list.filter((x) => x !== item) : [...list, item];
}

export function useDirectoryState() {
  const [params, setParams] = useSearchParams();
  const state = useMemo(() => parseDirectoryParams(params), [params]);

  useEffect(() => {
    rememberDirectorySearch(params.toString());
  }, [params]);

  const update = useCallback(
    (patch: (s: DirectoryState) => Partial<DirectoryState>) => {
      // replace : les réglages de filtre ne polluent pas l'historique (retour = page précédente)
      setParams(
        (prev) => {
          const current = parseDirectoryParams(prev);
          return serializeDirectoryState({ ...current, ...patch(current) });
        },
        { replace: true },
      );
    },
    [setParams],
  );

  return {
    state,
    setView: (view: DirectoryState['view']) => {
      update(() => ({ view }));
    },
    setName: (q: string) => {
      update(() => ({ q }));
    },
    toggleYear: (year: number) => {
      update((s) => ({ years: toggle(s.years, year) }));
    },
    clearYears: () => {
      update(() => ({ years: [] }));
    },
    toggleFiliere: (f: string) => {
      update((s) => ({ filieres: toggle(s.filieres, f) }));
    },
    clearFilieres: () => {
      update(() => ({ filieres: [] }));
    },
    toggleSchool: (sc: string) => {
      update((s) => ({ schools: toggle(s.schools, sc) }));
    },
    clearSchools: () => {
      update(() => ({ schools: [] }));
    },
    toggleAlphabetical: () => {
      update((s) => ({ alphabetical: !s.alphabetical }));
    },
    setFocus: (focus: number | null) => {
      update(() => ({ focus }));
    },
  };
}
