import { Avatar, Skeleton } from '../../../components/ui';
import { usePersonNavigation } from '../../../hooks/usePersonNavigation';
import { promoColor } from '../../../lib/colors';
import type { Person } from '../../../types/person';

const SKELETON_COUNT = 12;

const COL_GRID =
  'grid grid-cols-[36px_1fr] sm:grid-cols-[44px_1fr_160px] gap-3 sm:gap-4 px-4 sm:px-5 items-center';

function ListHeader() {
  return (
    <div
      className={`${COL_GRID} border-b border-line bg-bg py-2.5 text-[11px] font-semibold uppercase tracking-[0.06em] text-ink-3`}
    >
      <span />
      <span>Nom</span>
      <span className="hidden sm:block">Promotion</span>
    </div>
  );
}

function ListRowSkeleton({ last }: { last: boolean }) {
  return (
    <div className={`${COL_GRID} py-2.5 ${last ? '' : 'border-b border-line'}`}>
      <Skeleton className="h-8 w-8 rounded-[14%]" />
      <div>
        <Skeleton className="mb-1.5 h-4 w-40" />
        <Skeleton className="h-3 w-20 sm:hidden" />
      </div>
      <Skeleton className="hidden h-4 w-24 sm:block" />
    </div>
  );
}

function ListRow({ person, last }: { person: Person; last: boolean }) {
  const { navigateTo, isPending } = usePersonNavigation();
  const color = promoColor(person.startYear);

  return (
    <div
      onClick={() => {
        void navigateTo(person.id);
      }}
      className={`${COL_GRID} cursor-pointer py-2.5 transition-colors duration-100 hover:bg-bg ${last ? '' : 'border-b border-line'}`}
    >
      <div className="relative h-8 w-8 overflow-hidden rounded-[14%] sm:h-9 sm:w-9">
        <Avatar person={person} size={32} square />
        {isPending && (
          <div className="absolute inset-0 flex items-center justify-center rounded-[14%] bg-black/30">
            <svg
              className="animate-spin text-white"
              width="14"
              height="14"
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

      <div>
        <span className="text-[13.5px] font-medium text-ink">
          {person.firstName} {person.lastName}
        </span>
        {/* Promo visible uniquement sur mobile sous le nom */}
        <div className="mt-0.5 flex items-center gap-1.5 text-[11.5px] text-ink-3 sm:hidden">
          <span className="h-1.5 w-1.5 rounded-full" style={{ backgroundColor: color }} />
          {person.startYear} / {String(person.startYear + 1).slice(2)}
        </div>
      </div>

      {/* Promo en colonne sur desktop */}
      <div className="hidden items-center gap-2 text-[13px] text-ink-2 sm:flex">
        <span className="h-1.5 w-1.5 rounded-full" style={{ backgroundColor: color }} />
        {person.startYear} / {String(person.startYear + 1).slice(2)}
      </div>
    </div>
  );
}

interface ListViewProps {
  persons: Person[];
  loading: boolean;
}

export function ListView({ persons, loading }: ListViewProps) {
  return (
    <div
      data-testid="view-list-container"
      className="overflow-hidden rounded-xl border border-line bg-surface"
    >
      <ListHeader />
      {loading
        ? Array.from({ length: SKELETON_COUNT }, (_, i) => (
            <ListRowSkeleton key={i} last={i === SKELETON_COUNT - 1} />
          ))
        : persons.map((person, i) => (
            <ListRow key={person.id} person={person} last={i === persons.length - 1} />
          ))}
    </div>
  );
}
