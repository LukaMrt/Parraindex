import { useState } from 'react';
import { Avatar } from '../../../components/ui';
import { usePersonNavigation } from '../../../hooks/usePersonNavigation';
import { promoColor } from '../../../lib/colors';
import type { Person } from '../../../types/person';

interface PersonGridCardProps {
  person: Person;
  animationDelay?: number;
}

export function PersonGridCard({ person, animationDelay }: PersonGridCardProps) {
  const [hovered, setHovered] = useState(false);
  const { navigateTo, isPending } = usePersonNavigation();
  const color = promoColor(person.startYear);

  return (
    <article
      onClick={() => {
        void navigateTo(person.id);
      }}
      onMouseEnter={() => {
        setHovered(true);
      }}
      onMouseLeave={() => {
        setHovered(false);
      }}
      className="cursor-pointer overflow-hidden rounded-xl border border-line bg-surface transition-all duration-150"
      style={{
        borderColor: hovered ? color : undefined,
        transform: hovered ? 'translateY(-3px)' : undefined,
        boxShadow: hovered ? `0 8px 24px ${color}1A` : undefined,
        animationDelay: animationDelay !== undefined ? `${animationDelay}ms` : undefined,
      }}
    >
      <div className="h-[3px] w-full" style={{ backgroundColor: color }} />
      <div
        className="relative aspect-square overflow-hidden"
        style={{ backgroundColor: `${color}10` }}
      >
        <Avatar person={person} fill />
        {isPending && (
          <div className="absolute inset-0 flex items-center justify-center bg-black/30">
            <svg
              className="animate-spin text-white"
              width="22"
              height="22"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2.5"
              strokeLinecap="round"
            >
              <path d="M21 12a9 9 0 1 1-6.219-8.56" />
            </svg>
          </div>
        )}
      </div>
      <div className="p-3.5">
        <div className="text-[13.5px] font-semibold leading-tight tracking-[-0.005em] text-ink">
          {person.firstName} {person.lastName}
        </div>
        <div className="mt-1.5 flex items-center gap-1.5 text-[11.5px] text-ink-3">
          <span className="h-1.5 w-1.5 rounded-full" style={{ backgroundColor: color }} />
          {person.startYear} / {person.startYear + 1}
        </div>
      </div>
    </article>
  );
}
