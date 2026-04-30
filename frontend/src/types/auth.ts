import type { PersonSummary } from './person';

export interface Me {
  id: number;
  email: string;
  isAdmin: boolean;
  isVerified: boolean;
  person: PersonSummary;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  email: string;
  password: string;
}
