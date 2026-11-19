import type { CSSProperties, KeyboardEvent } from 'react';
import { Avatar } from '../ui';
import { promoColor } from '../../lib/colors';
import type { Person } from '../../types/person';

export type PersonSummary = Pick<
  Person,
  'id' | 'firstName' | 'lastName' | 'fullName' | 'picture' | 'startYear'
>;

interface PersonGraphNodeProps {
  person: PersonSummary;
  diameter: number;
  isSelf?: boolean;
  dim?: boolean;
  loading?: boolean;
  /** Position du centre du nœud dans le canvas */
  cx: number;
  cy: number;
  style?: CSSProperties;
  /** Libellé accessible de l'action (ex. « Voir la fiche de … ») */
  actionLabel?: string;
  onClick?: () => void;
  onHoverChange?: (hovered: boolean) => void;
}

/** "Thomas", "Bernard" → "Thomas B." : lève l'ambiguïté entre homonymes de prénom */
export function shortName(p: Pick<PersonSummary, 'firstName' | 'lastName'>): string {
  const first = p.firstName.charAt(0).toUpperCase() + p.firstName.slice(1).toLowerCase();
  const initial = p.lastName.charAt(0).toUpperCase();
  return initial ? `${first} ${initial}.` : first;
}

export function PersonGraphNode({
  person,
  diameter,
  isSelf = false,
  dim = false,
  loading = false,
  cx,
  cy,
  style,
  actionLabel,
  onClick,
  onHoverChange,
}: PersonGraphNodeProps) {
  const color = promoColor(person.startYear);
  const interactive = onClick !== undefined;

  const handleKeyDown = (e: KeyboardEvent) => {
    if (!onClick) return;
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      onClick();
    }
  };

  return (
    <div
      data-testid={`family-node-${String(person.id)}`}
      role={interactive ? 'button' : undefined}
      tabIndex={interactive ? 0 : undefined}
      aria-label={actionLabel ?? `${person.fullName}, promo ${String(person.startYear)}`}
      aria-busy={loading || undefined}
      title={`${person.fullName} · promo ${String(person.startYear)}`}
      onClick={onClick}
      onKeyDown={handleKeyDown}
      onMouseEnter={() => onHoverChange?.(true)}
      onMouseLeave={() => onHoverChange?.(false)}
      onFocus={() => onHoverChange?.(true)}
      onBlur={() => onHoverChange?.(false)}
      className="rounded-full outline-none focus-visible:ring-2 focus-visible:ring-ink focus-visible:ring-offset-2"
      style={{
        position: 'absolute',
        left: cx - diameter / 2,
        top: cy - diameter / 2,
        width: diameter,
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        gap: 4,
        cursor: interactive ? 'pointer' : 'default',
        opacity: dim ? 0.3 : 1,
        transition: 'opacity 0.15s',
        ...style,
      }}
    >
      <div
        style={{
          position: 'relative',
          width: diameter,
          height: diameter,
          borderRadius: '50%',
          overflow: 'hidden',
          flexShrink: 0,
          boxShadow: isSelf
            ? `0 0 0 3px ${color}, 0 0 0 6px ${color}25, 0 8px 18px ${color}30`
            : `0 0 0 2px ${color}, 0 2px 6px rgba(0,0,0,0.06)`,
        }}
      >
        <Avatar person={person} size={diameter} imageSize="thumb" />
        {loading && (
          <div
            style={{
              position: 'absolute',
              inset: 0,
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              background: 'rgba(0,0,0,0.35)',
              borderRadius: '50%',
            }}
          >
            <div
              style={{
                width: diameter * 0.38,
                height: diameter * 0.38,
                borderRadius: '50%',
                border: `2px solid rgba(255,255,255,0.35)`,
                borderTopColor: '#fff',
                animation: 'spin 0.7s linear infinite',
              }}
            />
          </div>
        )}
      </div>
      <div
        style={{
          fontSize: 10.5,
          fontWeight: isSelf ? 600 : 500,
          color: isSelf ? 'var(--color-ink)' : 'var(--color-ink-2)',
          textAlign: 'center',
          lineHeight: 1.15,
          maxWidth: 96,
          whiteSpace: 'nowrap',
          overflow: 'hidden',
          textOverflow: 'ellipsis',
          background: 'var(--color-surface)',
          padding: '2px 5px',
          borderRadius: 4,
          border: '1px solid var(--color-line)',
        }}
      >
        {shortName(person)}
      </div>
    </div>
  );
}
