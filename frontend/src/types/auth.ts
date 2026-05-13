import type { Person } from './person';

export interface Me {
  id: number;
  email: string;
  isAdmin: boolean;
  isValidated: boolean;
  person: Person;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  email: string;
  password: string;
  callbackUrl?: string;
  personId?: number;
}

export interface RegisterResponse {
  isValidated: boolean;
}

export interface AvailablePerson {
  id: number;
  firstName: string;
  lastName: string;
  fullName: string;
  startYear: number;
  color: string;
}
