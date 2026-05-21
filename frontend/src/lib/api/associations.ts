import { Result } from '../../types/api';
import { get } from './client';
export async function getAssociations(): Promise<Result<string[]>> {
  return get<string[]>('/api/associations');
}
