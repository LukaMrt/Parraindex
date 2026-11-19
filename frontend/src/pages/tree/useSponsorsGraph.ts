import { useMemo } from 'react';
import type { Person } from '../../types/person';
import type { Sponsor } from '../../types/sponsor';

/** Lien de parrainage complet (type, date, description) : rien n'est perdu pour l'affichage */
export type SponsorLink = Sponsor;

/** Tous les parrainages entre personnes chargées, dédupliqués par id */
export function useSponsorsGraph(persons: Person[]): SponsorLink[] {
  return useMemo(() => {
    const byId = new Map<number, Sponsor>();
    for (const p of persons) {
      for (const s of p.godChildren) byId.set(s.id, s);
      for (const s of p.godFathers) byId.set(s.id, s);
    }
    return [...byId.values()];
  }, [persons]);
}
