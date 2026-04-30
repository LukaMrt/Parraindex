import type { SponsorSummary } from './sponsor';

export interface PersonSummary {
  id: number;
  firstName: string;
  lastName: string;
  fullName: string;
  picture: string | null;
  color: string;
  startYear: number;
}

export interface Characteristic {
  id: number;
  value: string | null;
  visible: boolean;
  typeTitle: string;
  typeUrl: string | null;
}

export interface Person {
  id: number;
  firstName: string;
  lastName: string;
  fullName: string;
  picture: string | null;
  color: string;
  startYear: number;
  birthdate: string | null;
  biography: string | null;
  description: string | null;
  godFathers: SponsorSummary[];
  godChildren: SponsorSummary[];
  characteristics: Characteristic[];
}

export interface PersonRequest {
  firstName: string;
  lastName: string;
  startYear: number;
  biography: string | null;
  description: string | null;
  color: string | null;
}
