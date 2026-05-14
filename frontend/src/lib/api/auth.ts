import { get, post } from './client';
import type {
  Me,
  LoginRequest,
  RegisterRequest,
  RegisterResponse,
  AvailablePerson,
} from '../../types/auth';
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

export function register(data: RegisterRequest): Promise<Result<RegisterResponse>> {
  return post<RegisterResponse>('/api/auth/register', data);
}

export function getAvailablePersons(
  page: number,
  limit = 20,
): Promise<Result<{ items: AvailablePerson[]; total: number }>> {
  return get<{ items: AvailablePerson[]; total: number }>(
    `/api/auth/persons-available?page=${page}&limit=${limit}`,
  );
}

export function verifyEmail(queryString: string): Promise<Result<null>> {
  return get<null>(`/api/auth/verify-email?${queryString}`);
}

export function requestPasswordReset(email: string, callbackUrl: string): Promise<Result<null>> {
  return post<null>('/api/auth/reset-password/request', { email, callbackUrl });
}

export function confirmPasswordReset(token: string): Promise<Result<{ password: string }>> {
  return post<{ password: string }>('/api/auth/reset-password/confirm', { token });
}

export function resendVerificationEmail(callbackUrl: string): Promise<Result<null>> {
  return post<null>('/api/auth/resend-verification', { callbackUrl });
}
