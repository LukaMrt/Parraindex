import type { ApiError, Result } from '../../types/api';

function getCsrfToken(): string {
  const match = document.cookie.split('; ').find((row) => row.startsWith('XSRF-TOKEN='));
  return match ? decodeURIComponent(match.split('=')[1] ?? '') : '';
}

async function request<T>(method: string, url: string, body?: unknown): Promise<Result<T>> {
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    'X-XSRF-TOKEN': getCsrfToken(),
  };

  const response = await fetch(url, {
    method,
    headers,
    credentials: 'same-origin',
    body: body !== undefined ? JSON.stringify(body) : undefined,
  });

  if (response.status === 204) {
    return { ok: true, data: null as T };
  }

  const json: unknown = await response.json();

  if (!response.ok) {
    const errorPayload = json as { error: ApiError };
    return { ok: false, error: errorPayload.error };
  }

  const successPayload = json as { data: T };
  return { ok: true, data: successPayload.data };
}

export function get<T>(url: string): Promise<Result<T>> {
  return request<T>('GET', url);
}

export function post<T>(url: string, body?: unknown): Promise<Result<T>> {
  return request<T>('POST', url, body);
}

export function put<T>(url: string, body: unknown): Promise<Result<T>> {
  return request<T>('PUT', url, body);
}

export function del<T = null>(url: string): Promise<Result<T>> {
  return request<T>('DELETE', url);
}

export async function postFormData<T>(url: string, formData: FormData): Promise<Result<T>> {
  const response = await fetch(url, {
    method: 'POST',
    headers: { 'X-XSRF-TOKEN': getCsrfToken() },
    credentials: 'same-origin',
    body: formData,
  });

  if (!response.ok) {
    const json = (await response.json()) as { error: ApiError };
    return { ok: false, error: json.error };
  }

  const json = (await response.json()) as { data: T };
  return { ok: true, data: json.data };
}
