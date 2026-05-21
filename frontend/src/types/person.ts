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
  filieres: Filiere[];
  associations: Association[];
}

export interface FiliereRequest {
  name: string;
  startYear: number;
  endYear: number | null;
  schoolName: string | null;
}

export interface AssociationRequest {
  name: string;
  poste: string;
}

export interface PersonRequest {
  firstName: string;
  lastName: string;
  startYear: number;
  biography: string | null;
  description: string | null;
  filieres: FiliereRequest[];
  associations: AssociationRequest[];
}

export interface Filiere {
  _id?: string;
  name: string;
  color: string | null;
  startYear: number;
  endYear: number | null;
  schoolName: string | null;
  schoolLogoUrl: string | null;
}

export interface Association {
  _id?: string;
  name: string;
  logoUrl: string | null;
  poste: string;
}
