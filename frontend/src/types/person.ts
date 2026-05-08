import type { Sponsor } from './sponsor';

export interface Characteristic {
  id: number;
  value: string | null;
  visible: boolean;
  typeTitle: string;
  typeUrl: string | null;
  typeImage: string | null;
}

export interface Person {
  id: number;
  firstName: string;
  lastName: string;
  fullName: string;
  picture: string | null;
  startYear: number;
  birthdate: string | null;
  biography: string | null;
  description: string | null;
  godFathers: Sponsor[];
  godChildren: Sponsor[];
  characteristics: Characteristic[];
}

export interface PersonRequest {
  firstName: string;
  lastName: string;
  startYear: number;
  biography: string | null;
  description: string | null;
}
