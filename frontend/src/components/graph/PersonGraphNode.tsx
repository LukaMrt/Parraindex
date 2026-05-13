import type { CSSProperties } from 'react';
import { Avatar } from '../ui';
import { promoColor } from '../../lib/colors';
import type { Person } from '../../types/person';

interface PersonGraphNodeProps {
  person: Person;
  diameter: number;
  isSelf?: boolean;
  dim?: boolean;
  loading?: boolean;
  /** Nom de l'attribut data-* à poser sur le nœud (ex: "data-fg-node") pour le ciblage drag */
  dataAttr?: string;
  style?: CSSProperties;
  onClick?: () => void;
  onMouseEnter?: () => void;
  onMouseLeave?: () => void;
}

export function PersonGraphNode({
  person,
  diameter,
  isSelf = false,
  dim = false,
  loading = false,
  dataAttr,
  style,
  onClick,
  onMouseEnter,
  onMouseLeave,
}: PersonGraphNodeProps) {
  const color = promoColor(person.startYear);

  return (
    <div
      {...(dataAttr ? { [dataAttr]: true } : {})}
      onClick={onClick}
      onMouseEnter={onMouseEnter}
      onMouseLeave={onMouseLeave}
      style={{
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        gap: 4,
        cursor: isSelf ? 'default' : 'pointer',
        opacity: dim ? 0.3 : 1,
        transition: 'opacity 0.15s',
        width: diameter,
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
        <Avatar person={person} size={diameter} imageSize="full" />
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
          maxWidth: 72,
          whiteSpace: 'nowrap',
          overflow: 'hidden',
          textOverflow: 'ellipsis',
          background: 'var(--color-surface)',
          padding: '2px 5px',
          borderRadius: 4,
          border: '1px solid var(--color-line)',
        }}
      >
        {person.firstName}
      </div>
    </div>
  );
}
