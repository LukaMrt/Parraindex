const FALLBACK_AVATAR = '/images/icons/logo-blue.svg';

export function pictureUrl(picture: string | null): string {
  return picture !== null ? `/images/pictures/${picture}` : FALLBACK_AVATAR;
}
