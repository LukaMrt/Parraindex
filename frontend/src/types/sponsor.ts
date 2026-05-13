export type SponsorType = 'HEART' | 'CLASSIC' | 'UNKNOWN';

export interface Sponsor {
  id: number;
  godFatherId: number;
  godFatherName: string;
  godChildId: number;
  godChildName: string;
  type: SponsorType;
  date: string | null;
  description: string | null;
}

export interface SponsorRequest {
  godFatherId: number;
  godChildId: number;
  type: SponsorType;
  date: string | null;
  description: string | null;
}
