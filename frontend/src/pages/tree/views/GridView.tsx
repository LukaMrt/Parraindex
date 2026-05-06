import { Skeleton } from '../../../components/ui';
import { EmptyState } from '../../../components/ui';
import type { PersonSummary } from '../../../types/person';
import { PersonGridCard } from './PersonGridCard';

const SKELETON_COUNT = 18;

function PersonGridSkeleton() {
  return (
    <div className="overflow-hidden rounded-xl border border-line bg-surface">
      <div className="h-[3px] w-full animate-pulse bg-line" />
      <Skeleton className="aspect-square w-full rounded-none" />
      <div className="p-3.5">
        <Skeleton className="mb-2 h-[18px] w-3/4" />
        <Skeleton className="h-[15px] w-1/2" />
      </div>
    </div>
  );
}

interface GridViewProps {
  persons: PersonSummary[];
  loading: boolean;
}

export function GridView({ persons, loading }: GridViewProps) {
  const gridClass = 'grid gap-3.5' as const;
  const gridStyle = { gridTemplateColumns: 'repeat(auto-fill, minmax(180px, 1fr))' } as const;

  if (loading) {
    return (
      <div className={gridClass} style={gridStyle}>
        {Array.from({ length: SKELETON_COUNT }, (_, i) => (
          <PersonGridSkeleton key={i} />
        ))}
      </div>
    );
  }

  if (persons.length === 0) {
    return (
      <EmptyState
        title="Aucun résultat"
        description="Essayez de modifier vos filtres ou votre recherche."
      />
    );
  }

  return (
    <div className={gridClass} style={gridStyle}>
      {persons.map((person, i) => (
        <PersonGridCard key={person.id} person={person} animationDelay={Math.min(i * 20, 300)} />
      ))}
    </div>
  );
}
