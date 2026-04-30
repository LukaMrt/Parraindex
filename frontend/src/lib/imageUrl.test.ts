import { describe, it, expect } from 'vitest';
import { pictureUrl } from './imageUrl';

describe('pictureUrl', () => {
  it('retourne le chemin de la photo quand une photo existe', () => {
    expect(pictureUrl('avatar.jpg')).toBe('/images/pictures/avatar.jpg');
  });

  it('retourne le fallback quand la photo est null', () => {
    expect(pictureUrl(null)).toBe('/images/icons/logo-blue.svg');
  });
});
