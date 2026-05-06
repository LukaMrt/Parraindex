import { useMemo, type CSSProperties } from 'react';
import { useNavigate } from 'react-router';
import { Avatar, Skeleton } from '../../../components/ui';
import { promoColor } from '../../../lib/colors';
import type { PersonSummary } from '../../../types/person';

const SKELETON_GROUPS = [
  { year: 0, count: 5 },
  { year: 1, count: 8 },
  { year: 2, count: 6 },
];

function TimelinePersonSkeleton() {
  return (
    <div className="flex items-center gap-3.5 rounded-xl border border-line bg-surface px-4 py-3">
      <Skeleton className="h-11 w-11 shrink-0 rounded-[14%]" />
      <div className="flex-1">
        <Skeleton className="mb-2 h-3.5 w-28" />
        <Skeleton className="h-3 w-20" />
      </div>
    </div>
  );
}

function TimelinePerson({ person }: { person: PersonSummary }) {
  const navigate = useNavigate();
  const color = promoColor(person.startYear);

  return (
    <div
      onClick={() => {
        void navigate(`/personne/${person.id}`);
      }}
      className="flex cursor-pointer items-center gap-3.5 rounded-xl border border-line bg-surface px-4 py-3 transition-all duration-150 hover:border-[var(--hover-color)] hover:translate-x-0.5"
      style={{ '--hover-color': color } as CSSProperties}
    >
      <div className="h-11 w-11 shrink-0 overflow-hidden rounded-[14%]">
        <Avatar person={person} size={44} square />
      </div>
      <div className="min-w-0">
        <div className="truncate text-[13.5px] font-medium text-ink">{person.firstName}</div>
        <div className="truncate text-[12px] text-ink-3">{person.lastName}</div>
      </div>
    </div>
  );
}

interface TimelineViewProps {
  persons: PersonSummary[];
  loading: boolean;
}

export function TimelineView({ persons, loading }: TimelineViewProps) {
  const groups = useMemo(() => {
    const map = new Map<number, PersonSummary[]>();
    for (const p of persons) {
      const list = map.get(p.startYear) ?? [];
      list.push(p);
      map.set(p.startYear, list);
    }
    return Array.from(map.entries())
      .sort(([a], [b]) => a - b)
      .map(([year, list]) => ({ year, persons: list, color: promoColor(year) }));
  }, [persons]);

  if (loading) {
    return (
      <div className="flex flex-col gap-8">
        {SKELETON_GROUPS.map((g, gi) => (
          <div key={gi}>
            <div className="mb-3.5 flex items-baseline gap-3.5">
              <Skeleton className="h-7 w-28" />
              <Skeleton className="h-4 w-20" />
            </div>
            <div
              className="grid gap-2 pl-5"
              style={{
                gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))',
                borderLeft: '2px solid var(--color-line)',
              }}
            >
              {Array.from({ length: g.count }, (_, i) => (
                <TimelinePersonSkeleton key={i} />
              ))}
            </div>
          </div>
        ))}
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-8">
      {groups.map(({ year, persons: list, color }) => (
        <div key={year}>
          <div className="mb-3.5 flex items-baseline gap-3.5">
            <h3 className="text-[22px] font-semibold tracking-tight text-ink">Promo {year}</h3>
            <span className="text-[13px] text-ink-3">
              {list.length} étudiant{list.length > 1 ? 's' : ''} · {year} → {year + 1}
            </span>
          </div>
          <div
            className="grid gap-2 pl-5"
            style={{
              gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))',
              borderLeft: `2px solid ${color}`,
            }}
          >
            {list.map((person) => (
              <TimelinePerson key={person.id} person={person} />
            ))}
          </div>
        </div>
      ))}
    </div>
  );
}
