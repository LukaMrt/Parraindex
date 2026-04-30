export type ContactType =
  | 'ADD_PERSON'
  | 'UPDATE_PERSON'
  | 'REMOVE_PERSON'
  | 'ADD_SPONSOR'
  | 'REMOVE_SPONSOR'
  | 'OTHER';

export interface ContactRequest {
  contacterFirstName: string;
  contacterLastName: string;
  contacterEmail: string;
  type: ContactType;
  description: string;
  relatedPersonFirstName?: string;
  relatedPersonLastName?: string;
  relatedPerson2FirstName?: string;
  relatedPerson2LastName?: string;
  entryYear?: number;
  sponsorType?: string;
  sponsorDate?: string;
  registrationToken?: string;
}
