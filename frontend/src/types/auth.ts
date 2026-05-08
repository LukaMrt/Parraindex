import type { Person } from './person';

export interface Me {
  id: number;
  email: string;
  isAdmin: boolean;
  isVerified: boolean;
  person: Person;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  email: string;
  password: string;
}
