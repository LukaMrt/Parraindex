import { useEffect, useState } from 'react';
import { getPerson } from '../../lib/api/persons';
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

function personsKey(persons: PersonSummary[]): string {
  return persons
    .map((p) => p.id)
    .sort()
    .join(',');
}

export function useSponsorsGraph(persons: PersonSummary[]): SponsorsGraphResult {
  const [state, setState] = useState<{ key: string; links: SponsorLink[] }>({
    key: '',
    links: [],
  });

  useEffect(() => {
    if (persons.length === 0) return;

    const key = personsKey(persons);

    void Promise.all(persons.map((p) => getPerson(p.id))).then((results) => {
      const seen = new Set<string>();
      const deduped: SponsorLink[] = [];

      for (const result of results) {
        if (!result.ok) continue;
        for (const s of result.data.godChildren) {
          const k = `${s.godFatherId}-${s.godChildId}`;
          if (!seen.has(k)) {
            seen.add(k);
            deduped.push({ id: s.id, godFatherId: s.godFatherId, godChildId: s.godChildId });
          }
        }
      }

      setState({ key, links: deduped });
    });
  }, [persons]);

  const loading = persons.length > 0 && state.key !== personsKey(persons);

  return { links: state.links, loading };
}
