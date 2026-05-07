import { useState } from 'react';
import { useNavigate } from 'react-router';
import { Avatar } from '../../../components/ui';
import { promoColor } from '../../../lib/colors';
import type { PersonSummary } from '../../../types/person';

interface PersonGridCardProps {
  person: PersonSummary;
  animationDelay?: number;
}

export function PersonGridCard({ person, animationDelay }: PersonGridCardProps) {
  const [hovered, setHovered] = useState(false);
  const navigate = useNavigate();
  const color = promoColor(person.startYear);

  return (
    <article
      onClick={() => {
        void navigate(`/person/${person.id}`);
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
