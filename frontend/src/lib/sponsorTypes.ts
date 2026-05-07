import type { SponsorType } from '../types/sponsor';

export const SPONSOR_TYPE_ICONS: Record<SponsorType, string> = {
  HEART: '♥',
  CLASSIC: '⚒︎',
  UNKNOWN: '?',
};

export const SPONSOR_TYPE_LABELS: Record<SponsorType, string> = {
  HEART: 'de cœur',
  CLASSIC: 'IUT',
  UNKNOWN: 'inconnu',
};
