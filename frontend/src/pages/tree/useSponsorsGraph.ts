import type { Person } from '../../types/person';

export interface SponsorLink {
  id: number;
  godFatherId: number;
  godChildId: number;
}

export interface SponsorsGraphResult {
  links: SponsorLink[];
  loading: boolean;
}

export function useSponsorsGraph(persons: Person[]): SponsorsGraphResult {
  const seen = new Set<string>();
  const links: SponsorLink[] = [];

  for (const p of persons) {
    for (const s of p.godChildren) {
      const k = `${s.godFatherId}-${s.godChildId}`;
      if (!seen.has(k)) {
        seen.add(k);
        links.push({ id: s.id, godFatherId: s.godFatherId, godChildId: s.godChildId });
      }
    }
  }

  return { links, loading: false };
}
