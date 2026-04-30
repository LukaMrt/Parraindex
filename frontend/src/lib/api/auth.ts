import { get, post } from './client';
import type { Me, LoginRequest, RegisterRequest } from '../../types/auth';
import type { Result } from '../../types/api';

export function getMe(): Promise<Result<Me>> {
  return get<Me>('/api/auth/me');
}

export function login(credentials: LoginRequest): Promise<Result<Me>> {
  return post<Me>('/api/auth/login', credentials);
}

export function logout(): Promise<Result<null>> {
  return post<null>('/api/auth/logout');
}

export function register(data: RegisterRequest): Promise<Result<null>> {
  return post<null>('/api/auth/register', data);
}

export function verifyEmail(token: string): Promise<Result<null>> {
  return post<null>('/api/auth/verify-email', { token });
}

export function requestPasswordReset(email: string): Promise<Result<null>> {
  return post<null>('/api/auth/reset-password/request', { email });
}

export function confirmPasswordReset(token: string, password: string): Promise<Result<null>> {
  return post<null>('/api/auth/reset-password/confirm', { token, password });
}
