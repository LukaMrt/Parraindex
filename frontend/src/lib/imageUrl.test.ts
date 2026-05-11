import { describe, it, expect } from 'vitest';
import { pictureUrl } from './imageUrl';

describe('pictureUrl', () => {
  it('retourne la miniature LiipImagine par défaut', () => {
    expect(pictureUrl('abc123.jpg')).toBe('/media/cache/avatar_thumb/uploads/avatars/abc123.jpg');
  });

  it('retourne la version complète avec size="full"', () => {
    expect(pictureUrl('abc123.jpg', 'full')).toBe('/media/cache/avatar_full/uploads/avatars/abc123.jpg');
  });

  it('retourne le fallback quand la photo est null', () => {
    expect(pictureUrl(null)).toBe('/images/icons/logo-blue.svg');
  });
});
