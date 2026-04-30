import { post } from './client';
import type { ContactRequest } from '../../types/contact';
import type { Result } from '../../types/api';

export function submitContact(data: ContactRequest): Promise<Result<null>> {
  return post<null>('/api/contact', data);
}
