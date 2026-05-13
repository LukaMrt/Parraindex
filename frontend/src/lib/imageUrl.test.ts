import { describe, it, expect } from 'vitest';
import { pictureUrl } from './imageUrl';

describe('pictureUrl', () => {
  it('retourne la miniature LiipImagine par défaut', () => {
    expect(pictureUrl('a3f8b2c1d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9.jpg')).toBe(
      '/media/cache/resolve/avatar_thumb/uploads/avatars/a3f8b2c1d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9.jpg',
    );
  });

  it('retourne la version complète avec size="full"', () => {
    expect(pictureUrl('a3f8b2c1d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9.jpg', 'full')).toBe(
      '/media/cache/resolve/avatar_full/uploads/avatars/a3f8b2c1d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9.jpg',
    );
  });

  it('retourne le fallback quand la photo est null', () => {
    expect(pictureUrl(null)).toBe('/images/icons/logo-blue.svg');
  });
});
