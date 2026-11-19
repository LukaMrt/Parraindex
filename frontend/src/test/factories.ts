import type { Person } from '../types/person';
import type { Sponsor } from '../types/sponsor';

export function mkPerson(
  id: number,
  firstName: string,
  lastName: string,
  startYear: number,
  overrides: Partial<Person> = {},
): Person {
  return {
    id,
    firstName,
    lastName,
    fullName: `${firstName} ${lastName}`,
    startYear,
    picture: null,
    birthdate: null,
    biography: null,
    description: null,
    godFathers: [],
    godChildren: [],
    characteristics: [],
    filieres: [],
    associations: [],
    links: [],
    ...overrides,
  };
}

/** Relie parrain → fillot dans les deux fiches et renvoie le parrainage créé */
export function link(
  id: number,
  godFather: Person,
  godChild: Person,
  overrides: Partial<Sponsor> = {},
): Sponsor {
  const sponsor: Sponsor = {
    id,
    godFatherId: godFather.id,
    godFatherName: godFather.fullName,
    godChildId: godChild.id,
    godChildName: godChild.fullName,
    type: 'CLASSIC',
    date: '2021-09-01',
    description: null,
    ...overrides,
  };
  godFather.godChildren.push(sponsor);
  godChild.godFathers.push(sponsor);
  return sponsor;
}
