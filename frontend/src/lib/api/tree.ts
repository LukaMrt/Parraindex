import { get } from './client';
import type { PersonSummary } from '../../types/person';
import type { Result } from '../../types/api';

export function getTree(): Promise<Result<PersonSummary[]>> {
  return get<PersonSummary[]>('/api/tree');
}
