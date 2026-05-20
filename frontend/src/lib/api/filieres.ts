import { Result } from '../../types/api';
import { get } from './client';
export async function getFilieres(): Promise<Result<string[]>> {
  return get<string[]>('/api/filieres');
}
