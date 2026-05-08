import type { MouseEvent } from 'react';
import { pictureUrl } from '../lib/imageUrl';
import { promoColor } from '../lib/colors';
import type { Person } from '../types/person';

interface PersonCardProps {
  person: Person;
  isCentered?: boolean;
  onClick?: (e: MouseEvent) => void;
  animationDelay?: number;
}

export function PersonCard({
  person,
  isCentered = false,
  onClick,
  animationDelay,
}: PersonCardProps) {
  const color = promoColor(person.startYear);

  return (
    <article
      onClick={onClick}
      style={{
        animationDelay: animationDelay !== undefined ? `${animationDelay}ms` : undefined,
        boxShadow: isCentered ? `0 24px 64px -12px ${color}99` : undefined,
      }}
      className={[
        'card-fade-in relative flex w-48 shrink-0 flex-col overflow-hidden rounded-2xl bg-white transition-all duration-300',
        onClick !== undefined ? 'cursor-pointer' : '',
        isCentered ? 'z-10 scale-110' : 'scale-95 opacity-50 hover:opacity-70',
      ].join(' ')}
    >
      <div className="h-1 w-full shrink-0" style={{ backgroundColor: color }} />

      <div className="relative h-44 overflow-hidden" style={{ backgroundColor: `${color}22` }}>
        <img
          src={pictureUrl(person.picture)}
          alt={person.fullName}
          className="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
          loading="lazy"
        />
      </div>

      <div className="flex flex-col gap-0.5 p-4">
        <span className="text-sm font-bold uppercase tracking-wide text-dark-blue leading-tight">
          {person.lastName}
        </span>
        <span className="text-sm text-medium-blue">{person.firstName}</span>
        <span className="mt-2 text-xs font-medium" style={{ color }}>
          {person.startYear} / {person.startYear + 1}
        </span>
      </div>
    </article>
  );
}
