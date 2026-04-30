import type { ContactType } from './contact';

export interface AdminContact {
  id: number;
  type: ContactType;
  typeLabel: string;
  createdAt: string;
  resolutionDate: string | null;
  contacterFirstName: string;
  contacterLastName: string;
  contacterEmail: string;
  relatedPersonFirstName: string | null;
  relatedPersonLastName: string | null;
  relatedPerson2FirstName: string | null;
  relatedPerson2LastName: string | null;
  description: string;
}
