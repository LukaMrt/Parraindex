import { useQueryClient } from '@tanstack/react-query';
import { useEffect, useMemo, useState } from 'react';
import type { Dispatch, SetStateAction } from 'react';
import { usePanZoom } from '../../hooks/usePanZoom';
import { fetchPersonsBatch } from '../../lib/api/persons';
import { personQueries } from '../../lib/queries';
import type { UsePanZoomResult } from '../../hooks/usePanZoom';
import type { Person } from '../../types/person';
import { COL_W, computeLayout, toSummary } from './familyGraphLayout';
import type { Layout } from './familyGraphLayout';

export interface FamilyGraphState extends UsePanZoomResult {
  layout: Layout;
  containerHeight: number;
  initialLoading: boolean;
  loadingAncestors: boolean;
  loadingDescendants: boolean;
  hoverId: number | null;
  ancestorGens: Person[][];
  descendantGens: Person[][];
  canExpandAncestors: boolean;
  canExpandDescendants: boolean;
  setHoverId: (id: number | null) => void;
  setZoom: Dispatch<SetStateAction<number>>;
  setPan: Dispatch<SetStateAction<{ x: number; y: number }>>;
  shrinkAncestors: () => void;
  expandAncestors: () => void;
  shrinkDescendants: () => void;
  expandDescendants: () => void;
}

export function useFamilyGraph(person: Person): FamilyGraphState {
  const queryClient = useQueryClient();

  const batchFetch = (ids: number[]): Promise<Map<number, Person>> => {
    const cached = new Map<number, Person>();
    const missing: number[] = [];

    for (const id of ids) {
      const hit = queryClient.getQueryData<Person>(personQueries.detail(id).queryKey);
      if (hit) cached.set(id, hit);
      else missing.push(id);
    }

    if (missing.length === 0) return Promise.resolve(cached);

    return fetchPersonsBatch(missing).then((result) => {
      if (result.ok) {
        result.data.forEach((p) => {
          queryClient.setQueryData(personQueries.detail(p.id).queryKey, p);
          cached.set(p.id, p);
        });
      }
      return cached;
    });
  };
  const directIds = useMemo(
    () => [
      ...person.godFathers.map((s) => s.godFatherId),
      ...person.godChildren.map((s) => s.godChildId),
    ],
    [person],
  );

  const [fetchedPersons, setFetchedPersons] = useState<Map<number, Person>>(
    () => new Map([[person.id, person]]),
  );
  const [ancestorGens, setAncestorGens] = useState<Person[][]>([]);
  const [descendantGens, setDescendantGens] = useState<Person[][]>([]);
  const [initialLoading, setInitialLoading] = useState(() => directIds.length > 0);
  const [loadingAncestors, setLoadingAncestors] = useState(false);
  const [loadingDescendants, setLoadingDescendants] = useState(false);
  const [hoverId, setHoverId] = useState<number | null>(null);

  const panZoom = usePanZoom({ dragBlockSelector: '[data-fg-node]', minZoom: 0.4 });

  // ── Initial fetch: load direct family members

  useEffect(() => {
    if (directIds.length === 0) return;

    void batchFetch(directIds).then((fetched) => {
      setFetchedPersons((prev) => new Map([...prev, ...fetched]));

      const parents = person.godFathers
        .map((s) => fetched.get(s.godFatherId))
        .filter((p): p is Person => p !== undefined)
        .map(toSummary);
      const children = person.godChildren
        .map((s) => fetched.get(s.godChildId))
        .filter((p): p is Person => p !== undefined)
        .map(toSummary);

      if (parents.length > 0) setAncestorGens([parents]);
      if (children.length > 0) setDescendantGens([children]);
      setInitialLoading(false);
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [directIds]);

  // ── Layout

  const rootSummary = useMemo(() => toSummary(person), [person]);

  const layout = useMemo(
    () => computeLayout(rootSummary, ancestorGens, descendantGens, fetchedPersons),
    [rootSummary, ancestorGens, descendantGens, fetchedPersons],
  );

  const containerHeight = Math.max(320, Math.min(580, 80 + layout.rowCount * COL_W));

  // ── Depth expansion

  const canExpandAncestors = useMemo(() => {
    const frontier =
      ancestorGens.length > 0 ? (ancestorGens[ancestorGens.length - 1] ?? []) : [rootSummary];
    const existingIds = new Set([person.id, ...ancestorGens.flat().map((p) => p.id)]);
    return frontier.some((p) =>
      (fetchedPersons.get(p.id)?.godFathers ?? []).some((s) => !existingIds.has(s.godFatherId)),
    );
  }, [ancestorGens, fetchedPersons, rootSummary, person.id]);

  const canExpandDescendants = useMemo(() => {
    const frontier =
      descendantGens.length > 0 ? (descendantGens[descendantGens.length - 1] ?? []) : [rootSummary];
    const existingIds = new Set([person.id, ...descendantGens.flat().map((p) => p.id)]);
    return frontier.some((p) =>
      (fetchedPersons.get(p.id)?.godChildren ?? []).some((s) => !existingIds.has(s.godChildId)),
    );
  }, [descendantGens, fetchedPersons, rootSummary, person.id]);

  const expandAncestors = () => {
    const frontier =
      ancestorGens.length > 0 ? (ancestorGens[ancestorGens.length - 1] ?? []) : [rootSummary];
    const existingIds = new Set([person.id, ...ancestorGens.flat().map((p) => p.id)]);
    const newIds = [
      ...new Set(
        frontier
          .flatMap((p) => fetchedPersons.get(p.id)?.godFathers.map((s) => s.godFatherId) ?? [])
          .filter((id) => !existingIds.has(id)),
      ),
    ];
    if (newIds.length === 0) return;

    setLoadingAncestors(true);
    void batchFetch(newIds).then((fetched) => {
      setFetchedPersons((prev) => new Map([...prev, ...fetched]));
      const allFetched = new Map([...fetchedPersons, ...fetched]);
      const newGen = newIds
        .map((id) => allFetched.get(id))
        .filter((p): p is Person => p !== undefined)
        .map(toSummary);
      setAncestorGens((prev) => [...prev, newGen]);
      setLoadingAncestors(false);
    });
  };

  const expandDescendants = () => {
    const frontier =
      descendantGens.length > 0 ? (descendantGens[descendantGens.length - 1] ?? []) : [rootSummary];
    const existingIds = new Set([person.id, ...descendantGens.flat().map((p) => p.id)]);
    const newIds = [
      ...new Set(
        frontier
          .flatMap((p) => fetchedPersons.get(p.id)?.godChildren.map((s) => s.godChildId) ?? [])
          .filter((id) => !existingIds.has(id)),
      ),
    ];
    if (newIds.length === 0) return;

    setLoadingDescendants(true);
    void batchFetch(newIds).then((fetched) => {
      setFetchedPersons((prev) => new Map([...prev, ...fetched]));
      const allFetched = new Map([...fetchedPersons, ...fetched]);
      const newGen = newIds
        .map((id) => allFetched.get(id))
        .filter((p): p is Person => p !== undefined)
        .map(toSummary);
      setDescendantGens((prev) => [...prev, newGen]);
      setLoadingDescendants(false);
    });
  };

  return {
    ...panZoom,
    layout,
    containerHeight,
    initialLoading,
    loadingAncestors,
    loadingDescendants,
    hoverId,
    ancestorGens,
    descendantGens,
    canExpandAncestors,
    canExpandDescendants,
    setHoverId,
    shrinkAncestors: () => {
      setAncestorGens((prev) => prev.slice(0, -1));
    },
    expandAncestors,
    shrinkDescendants: () => {
      setDescendantGens((prev) => prev.slice(0, -1));
    },
    expandDescendants,
  };
}
