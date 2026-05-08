import { useQueries } from '@tanstack/react-query';
import { personQueries } from '../../lib/queries';
import type { PersonSummary } from '../../types/person';

export interface SponsorLink {
  id: number;
  godFatherId: number;
  godChildId: number;
}

export interface SponsorsGraphResult {
  links: SponsorLink[];
  loading: boolean;
}

export function useSponsorsGraph(persons: PersonSummary[]): SponsorsGraphResult {
  const results = useQueries({
    queries: persons.map((p) => ({
      ...personQueries.detail(p.id),
      staleTime: 5 * 60 * 1000,
    })),
  });

  const loading = results.some((r) => r.isLoading);

  const seen = new Set<string>();
  const links: SponsorLink[] = [];
  for (const r of results) {
    if (!r.data) continue;
    for (const s of r.data.godChildren) {
      const k = `${s.godFatherId}-${s.godChildId}`;
      if (!seen.has(k)) {
        seen.add(k);
        links.push({ id: s.id, godFatherId: s.godFatherId, godChildId: s.godChildId });
      }
    }
  }

  return { links, loading };
}
