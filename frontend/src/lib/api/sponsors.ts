import { get, post, put, del } from './client';
import type { Sponsor, SponsorRequest } from '../../types/sponsor';
import type { Result } from '../../types/api';

export function getSponsor(id: number): Promise<Result<Sponsor>> {
  return get<Sponsor>(`/api/sponsors/${id}`);
}

export function createSponsor(data: SponsorRequest): Promise<Result<Sponsor>> {
  return post<Sponsor>('/api/sponsors', data);
}

export function updateSponsor(id: number, data: SponsorRequest): Promise<Result<Sponsor>> {
  return put<Sponsor>(`/api/sponsors/${id}`, data);
}

export function deleteSponsor(id: number): Promise<Result<null>> {
  return del(`/api/sponsors/${id}`);
}
