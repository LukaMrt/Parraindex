import type { MouseEvent } from 'react';
import { pictureUrl } from '../lib/imageUrl';
import type { PersonSummary } from '../types/person';

interface PersonCardProps {
  person: PersonSummary;
  isCentered?: boolean;
  onClick?: (e: MouseEvent) => void;
}

export function PersonCard({ person, isCentered = false, onClick }: PersonCardProps) {
  return (
    <article
      onClick={onClick}
      className={[
        'flex w-52 shrink-0 flex-col overflow-hidden rounded-lg bg-white shadow transition-transform',
        onClick !== undefined ? 'cursor-pointer' : '',
        isCentered ? 'scale-105 shadow-md' : 'opacity-80',
      ].join(' ')}
    >
      <div className="relative h-36" style={{ backgroundColor: person.color }}>
        <img
          src={pictureUrl(person.picture)}
          alt={person.fullName}
          className="h-full w-full object-cover"
          loading="lazy"
        />
      </div>

      <div className="flex flex-col gap-1 p-3">
        <span className="text-sm font-bold uppercase text-dark-blue">{person.lastName}</span>
        <span className="text-sm text-medium-blue">{person.firstName}</span>
        <span className="mt-auto text-xs text-dark-grey">{person.startYear}</span>
      </div>
    </article>
  );
}
