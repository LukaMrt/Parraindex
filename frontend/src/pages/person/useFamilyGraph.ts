import { useQueryClient } from '@tanstack/react-query';
import { useCallback, useEffect, useMemo, useState } from 'react';
import type { PersonSummary } from '../../components/graph/PersonGraphNode';
import { fetchPersonsBatch } from '../../lib/api/persons';
import { personQueries } from '../../lib/queries';
import type { Person } from '../../types/person';
import { computeLayout, toSummary } from './familyGraphLayout';
import type { Layout } from './familyGraphLayout';

export interface FamilyGraphState {
  layout: Layout;
  initialLoading: boolean;
  /** Erreur de chargement (initial ou expansion), null sinon */
  error: string | null;
  loadingAncestors: boolean;
  loadingDescendants: boolean;
  ancestorDepth: number;
  descendantDepth: number;
  canExpandAncestors: boolean;
  canExpandDescendants: boolean;
  fetchedPersons: Map<number, Person>;
  retry: () => void;
  shrinkAncestors: () => void;
  expandAncestors: () => void;
  shrinkDescendants: () => void;
  expandDescendants: () => void;
}

const LOAD_ERROR = "Impossible de charger l'arbre. Vérifiez votre connexion.";

/**
 * État de l'arbre familial d'une personne : générations chargées à la demande
 * (« Amont » = parrains de parrains…, « Aval » = fillots de fillots…).
 *
 * Le composant appelant est remonté à chaque changement de personne (`key={person.id}`) :
 * l'état n'est donc jamais réinitialisé par un simple rafraîchissement des données.
 */
export function useFamilyGraph(person: Person): FamilyGraphState {
  const queryClient = useQueryClient();

  const batchFetch = useCallback(
    async (ids: number[]): Promise<Map<number, Person>> => {
      const result = new Map<number, Person>();
      const missing: number[] = [];
      for (const id of ids) {
        const hit = queryClient.getQueryData<Person>(personQueries.detail(id).queryKey);
        if (hit) result.set(id, hit);
        else missing.push(id);
      }
      if (missing.length === 0) return result;

      const res = await fetchPersonsBatch(missing);
      if (!res.ok) throw new Error(res.error.message);
      for (const p of res.data) {
        queryClient.setQueryData(personQueries.detail(p.id).queryKey, p);
        result.set(p.id, p);
      }
      return result;
    },
    [queryClient],
  );

  // Instantané de la racine au montage : sert à amorcer le chargement initial une seule fois
  const [initialRoot] = useState(person);

  const [loaded, setLoaded] = useState<Map<number, Person>>(() => new Map());
  const [ancestorGens, setAncestorGens] = useState<PersonSummary[][]>([]);
  const [descendantGens, setDescendantGens] = useState<PersonSummary[][]>([]);
  const [initialLoading, setInitialLoading] = useState(
    () => person.godFathers.length + person.godChildren.length > 0,
  );
  const [error, setError] = useState<string | null>(null);
  const [attempt, setAttempt] = useState(0);
  const [loadingAncestors, setLoadingAncestors] = useState(false);
  const [loadingDescendants, setLoadingDescendants] = useState(false);

  // La racine affichée est toujours la version la plus récente
  const fetchedPersons = useMemo(() => {
    const map = new Map(loaded);
    map.set(person.id, person);
    return map;
  }, [loaded, person]);

  const mergeLoaded = (fetched: Map<number, Person>) => {
    setLoaded((prev) => new Map([...prev, ...fetched]));
  };

  // ── Chargement initial : parrains et fillots directs

  useEffect(() => {
    const parentIds = initialRoot.godFathers.map((s) => s.godFatherId);
    const childIds = initialRoot.godChildren.map((s) => s.godChildId);
    if (parentIds.length + childIds.length === 0) return;

    let cancelled = false;
    batchFetch([...parentIds, ...childIds])
      .then((fetched) => {
        if (cancelled) return;
        setLoaded((prev) => new Map([...prev, ...fetched]));
        const pick = (ids: number[]) =>
          ids.map((id) => fetched.get(id)).filter((p): p is Person => p !== undefined);
        const parents = pick(parentIds).map(toSummary);
        const children = pick(childIds).map(toSummary);
        setAncestorGens(parents.length > 0 ? [parents] : []);
        setDescendantGens(children.length > 0 ? [children] : []);
        setError(null);
        setInitialLoading(false);
      })
      .catch(() => {
        if (cancelled) return;
        setError(LOAD_ERROR);
        setInitialLoading(false);
      });
    return () => {
      cancelled = true;
    };
  }, [initialRoot, batchFetch, attempt]);

  // ── Layout

  const rootSummary = useMemo(() => toSummary(person), [person]);

  const layout = useMemo(
    () => computeLayout(rootSummary, ancestorGens, descendantGens, fetchedPersons),
    [rootSummary, ancestorGens, descendantGens, fetchedPersons],
  );

  // ── Expansion

  /** Tous les nœuds affichés (amont + racine + aval) */
  const displayedIds = useMemo(
    () => new Set([person.id, ...ancestorGens.flat(), ...descendantGens.flat()].map(idOf)),
    [person.id, ancestorGens, descendantGens],
  );

  /** Prochains parrains (resp. fillots) à charger, d'après tous les nœuds du côté concerné */
  const nextIds = (side: 'up' | 'down'): number[] => {
    const sideNodes = [
      person.id,
      ...(side === 'up' ? ancestorGens : descendantGens).flat().map(idOf),
    ];
    const ids = sideNodes.flatMap((id) => {
      const p = fetchedPersons.get(id);
      if (!p) return [];
      return side === 'up'
        ? p.godFathers.map((s) => s.godFatherId)
        : p.godChildren.map((s) => s.godChildId);
    });
    return [...new Set(ids.filter((id) => !displayedIds.has(id)))];
  };

  // « + » n'est actif que s'il ajoute réellement des personnes
  const canExpandAncestors = !initialLoading && error === null && nextIds('up').length > 0;
  const canExpandDescendants = !initialLoading && error === null && nextIds('down').length > 0;

  const expand = (side: 'up' | 'down') => {
    const setGens = side === 'up' ? setAncestorGens : setDescendantGens;
    const setLoading = side === 'up' ? setLoadingAncestors : setLoadingDescendants;
    const ids = nextIds(side);
    if (ids.length === 0) return;
    setLoading(true);
    batchFetch(ids)
      .then((fetched) => {
        mergeLoaded(fetched);
        const gen = ids
          .map((id) => fetched.get(id))
          .filter((p): p is Person => p !== undefined)
          .map(toSummary);
        if (gen.length > 0) setGens((prev) => [...prev, gen]);
      })
      .catch(() => {
        setError(LOAD_ERROR);
      })
      .finally(() => {
        setLoading(false);
      });
  };

  return {
    layout,
    initialLoading,
    error,
    loadingAncestors,
    loadingDescendants,
    ancestorDepth: ancestorGens.length,
    descendantDepth: descendantGens.length,
    canExpandAncestors,
    canExpandDescendants,
    fetchedPersons,
    retry: () => {
      setError(null);
      if (ancestorGens.length + descendantGens.length === 0) {
        setInitialLoading(true);
        setAttempt((n) => n + 1);
      }
    },
    shrinkAncestors: () => {
      setAncestorGens((prev) => prev.slice(0, -1));
    },
    expandAncestors: () => {
      expand('up');
    },
    shrinkDescendants: () => {
      setDescendantGens((prev) => prev.slice(0, -1));
    },
    expandDescendants: () => {
      expand('down');
    },
  };
}

function idOf(p: PersonSummary | number): number {
  return typeof p === 'number' ? p : p.id;
}
