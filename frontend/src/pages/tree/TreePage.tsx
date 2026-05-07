import { useMemo, useState } from 'react';
import { useSearchParams } from 'react-router';
import { usePersonFilter } from '../../hooks/usePersonFilter';
import { getYearRange } from '../../lib/persons';
import { DirectoryToolbar } from './toolbar/DirectoryToolbar';
import type { DirectoryView } from './types';
import { usePersons } from './usePersons';
import { useSponsorsGraph } from './useSponsorsGraph';
import { GridView } from './views/GridView';
import { ListView } from './views/ListView';
import { TimelineView } from './views/TimelineView';
import { TreeView } from './views/TreeView';

export function TreePage() {
  const { persons, loading } = usePersons();
  const { links, loading: linksLoading } = useSponsorsGraph(persons);
  const [view, setView] = useState<DirectoryView>('grid');
  const [searchParams] = useSearchParams();
  const initialYear = searchParams.get('year') ? Number(searchParams.get('year')) : null;

  const {
    name,
    years: selectedYears,
    alphabetical,
    filtered,
    setName,
    toggleYear,
    clearYears,
    toggleAlphabetical,
  } = usePersonFilter(persons, initialYear !== null ? [initialYear] : []);

  const yearRange = getYearRange(persons);
  const availableYears = useMemo(() => {
    if (!yearRange) return [];
    return Array.from({ length: yearRange.max - yearRange.min + 1 }, (_, i) => yearRange.min + i);
  }, [yearRange]);

  return (
    <div className="flex min-h-[calc(100vh-var(--header-height))] flex-col bg-bg">
      {/* En-tête */}
      <div className="border-b border-line bg-surface px-7 pb-5 pt-7">
        <h1 className="text-[30px] font-semibold tracking-tight text-ink">Annuaire</h1>
        <p className="mt-1 text-[14px] text-ink-3">
          {loading
            ? 'Chargement…'
            : `${persons.length} étudiant${persons.length > 1 ? 's' : ''} · ${availableYears.length} promotion${availableYears.length > 1 ? 's' : ''}`}
        </p>
      </div>

      <DirectoryToolbar
        search={name}
        onSearchChange={setName}
        view={view}
        onViewChange={setView}
        alphabetical={alphabetical}
        onToggleAlphabetical={toggleAlphabetical}
        years={availableYears}
        selectedYears={selectedYears}
        onToggleYear={toggleYear}
        onClearYears={clearYears}
        resultCount={filtered.length}
        loading={loading}
      />

      {/* Contenu */}
      <div className="flex-1 px-7 py-6">
        {view === 'grid' && <GridView persons={filtered} loading={loading} />}
        {view === 'list' && <ListView persons={filtered} loading={loading} />}
        {view === 'timeline' && <TimelineView persons={filtered} loading={loading} />}
        {view === 'tree' && (
          <TreeView persons={filtered} links={links} loading={loading || linksLoading} />
        )}
      </div>
    </div>
  );
}
