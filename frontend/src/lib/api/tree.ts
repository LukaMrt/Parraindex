import { get } from './client';
import type { Person } from '../../types/person';
import type { Result } from '../../types/api';

export interface TreePageResult {
  items: Person[];
  total: number;
}

export function getTreePage(page: number, limit: number): Promise<Result<TreePageResult>> {
  return get<TreePageResult>(`/api/tree?page=${page}&limit=${limit}`);
}
