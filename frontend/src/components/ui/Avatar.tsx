import { useState } from 'react';
import { cn } from '../../lib/cn';
import { promoColor } from '../../lib/colors';
import { pictureUrl } from '../../lib/imageUrl';
import type { Person } from '../../types/person';

interface AvatarProps {
  person: Pick<Person, 'firstName' | 'lastName' | 'fullName' | 'picture' | 'startYear'>;
  size?: number;
  /** true = coins arrondis (carré), false = rond */
  square?: boolean;
  /** Remplit le conteneur parent (pour les cartes grille) */
  fill?: boolean;
  className?: string;
}

export function Avatar({
  person,
  size = 80,
  square = false,
  fill = false,
  className,
}: AvatarProps) {
  const [imgError, setImgError] = useState(false);

  const color = promoColor(person.startYear);
  const initials = ((person.firstName[0] ?? '') + (person.lastName[0] ?? '')).toUpperCase();
  const radius = square ? '14%' : '50%';
  const dims = fill ? { width: '100%', height: '100%' } : { width: size, height: size };
  const fontSize = fill ? 32 : size * 0.36;

  if (person.picture && !imgError) {
    return (
      <img
        src={pictureUrl(person.picture)}
        alt={person.fullName}
        loading="lazy"
        onError={() => {
          setImgError(true);
        }}
        className={cn('block shrink-0 object-cover', className)}
        style={{ ...dims, borderRadius: fill ? 0 : radius }}
      />
    );
  }

  return (
    <div
      className={cn('flex shrink-0 items-center font-semibold', className)}
      style={{
        ...dims,
        borderRadius: fill ? 0 : radius,
        background: color + '1F',
        color,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontSize,
        letterSpacing: '0.02em',
      }}
    >
      {initials}
    </div>
  );
}
