import { get, post, put, patch, del, postFormData } from './client';
import type { Person, PersonRequest } from '../../types/person';
import type { Result } from '../../types/api';

export type PersonOrderBy = 'id' | 'firstName' | 'lastName' | 'startYear' | 'createdAt';

export function getPersons(orderBy: PersonOrderBy = 'id'): Promise<Result<Person[]>> {
  return get<Person[]>(`/api/persons?orderBy=${orderBy}`);
}

export function getPerson(id: number): Promise<Result<Person>> {
  return get<Person>(`/api/persons/${id}`);
}

export function updatePerson(id: number, data: PersonRequest): Promise<Result<Person>> {
  return put<Person>(`/api/persons/${id}`, data);
}

export function deletePerson(id: number): Promise<Result<null>> {
  return del(`/api/persons/${id}`);
}

export function uploadPicture(id: number, file: File): Promise<Result<{ picture: string }>> {
  const formData = new FormData();
  formData.append('picture', file);
  return postFormData<{ picture: string }>(`/api/persons/${id}/picture`, formData);
}

export interface AccountUpdateRequest {
  email?: string;
  currentPassword?: string;
  newPassword?: string;
}

export function getAccount(personId: number): Promise<Result<{ email: string | null }>> {
  return get<{ email: string | null }>(`/api/persons/${personId}/account`);
}

export function updateAccount(personId: number, data: AccountUpdateRequest): Promise<Result<null>> {
  return patch<null>(`/api/persons/${personId}/account`, data);
}

export function fetchPersonsBatch(ids: number[]): Promise<Result<Person[]>> {
  if (ids.length === 0) return Promise.resolve({ ok: true as const, data: [] });
  return post<Person[]>('/api/persons/batch', { ids });
}
