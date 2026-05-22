import { get } from './client';
import type { Result } from '../../types/api';

export interface CharacteristicType {
  id: number;
  title: string;
  url: string | null;
  image: string | null;
}

export function getCharacteristicTypes(): Promise<Result<CharacteristicType[]>> {
  return get<CharacteristicType[]>('/api/characteristic-types');
}
