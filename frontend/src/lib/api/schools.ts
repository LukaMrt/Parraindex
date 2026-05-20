import { Result } from '../../types/api';
import { get } from './client';
export async function getSchools(): Promise<Result<string[]>> {
  return get<string[]>('/api/schools');
}
