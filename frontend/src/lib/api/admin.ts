import { del, get, put } from './client';
import type { AdminContact } from '../../types/admin';
import type { Result } from '../../types/api';

export function getAdminContacts(): Promise<Result<AdminContact[]>> {
  return get<AdminContact[]>('/api/admin/contacts');
}

export function resolveContact(id: number): Promise<Result<null>> {
  return put<null>(`/api/admin/contacts/${id}/resolve`, {});
}

export function closeContact(id: number): Promise<Result<null>> {
  return del(`/api/admin/contacts/${id}`);
}
