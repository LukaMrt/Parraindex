const FALLBACK_AVATAR = '/images/icons/logo-blue.svg';

export function pictureUrl(picture: string | null, size: 'thumb' | 'full' = 'thumb'): string {
  if (picture === null) return FALLBACK_AVATAR;
  // GIF (chemin direct renvoyé par le backend) ou blob: preview local
  if (picture.startsWith('/') || picture.startsWith('blob:')) return picture;
  const filter = size === 'thumb' ? 'avatar_thumb' : 'avatar_full';
  return `/media/cache/resolve/${filter}/uploads/avatars/${picture}`;
}
