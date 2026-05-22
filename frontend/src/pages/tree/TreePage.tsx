import { useMemo, useReducer, useEffect, useState } from 'react';
import { useSearchParams } from 'react-router';
import { usePersonFilter } from '../../hooks/usePersonFilter';
import { DirectoryToolbar } from './toolbar/DirectoryToolbar';
import type { DirectoryView } from './types';
import type { Person } from '../../types/person';
import { usePersons } from './usePersons';
import { useSponsorsGraph } from './useSponsorsGraph';
import { GridView } from './views/GridView';
import { ListView } from './views/ListView';
import { TimelineView } from './views/TimelineView';
import { TreeView } from './views/TreeView';
import { EgoGraphView } from './views/EgoGraphView';

interface ShuffleAction {
  type: 'SET';
  persons: Person[];
}

function shuffleReducer(state: Person[], action: ShuffleAction): Person[] {
  if (state.length > 0) return state;
  const copy = [...action.persons];
  for (let i = copy.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    const a = copy[i];
    const b = copy[j];
    if (a !== undefined && b !== undefined) {
      copy[i] = b;
      copy[j] = a;
    }
  }
  return copy;
}

export function TreePage() {
  const { persons, loading, loadingMore } = usePersons();
  const { links, loading: linksLoading } = useSponsorsGraph(persons);
  const [view, setView] = useState<DirectoryView>('grid');
  const [shuffledPersons, dispatch] = useReducer(shuffleReducer, []);

  useEffect(() => {
    if (!loading && !loadingMore && persons.length > 0) {
      dispatch({ type: 'SET', persons });
    }
  }, [loading, loadingMore, persons]);

  const displayPersons = shuffledPersons.length > 0 ? shuffledPersons : persons;
  const [searchParams] = useSearchParams();
  const initialYear = searchParams.get('year') ? Number(searchParams.get('year')) : null;

  const {
    name,
    years: selectedYears,
    alphabetical,
    filieres: selectedFilieres,
    schools: selectedSchools,
    filtered,
    setName,
    toggleYear,
    clearYears,
    toggleAlphabetical,
    toggleFiliere,
    clearFilieres,
    toggleSchool,
    clearSchools,
  } = usePersonFilter(displayPersons, initialYear !== null ? [initialYear] : []);

  const availableYears = useMemo(() => {
    const yearSet = new Set(persons.map((p) => p.startYear));
    return Array.from(yearSet).sort((a, b) => a - b);
  }, [persons]);

  const availableFilieres = useMemo(() => {
    const set = new Set<string>();
    for (const p of persons) {
      for (const f of p.filieres) set.add(f.name);
    }
    return Array.from(set).sort((a, b) => a.localeCompare(b, 'fr'));
  }, [persons]);

  const availableSchools = useMemo(() => {
    const set = new Set<string>();
    for (const p of persons) {
      for (const f of p.filieres) {
        if (f.schoolName) set.add(f.schoolName);
      }
    }
    return Array.from(set).sort((a, b) => a.localeCompare(b, 'fr'));
  }, [persons]);

  return (
    <div className="flex min-h-[calc(100vh-var(--header-height))] flex-col bg-bg">
      {/* En-tête */}
      <div className="border-b border-line bg-surface px-4 pb-5 pt-6 sm:px-7 sm:pt-7">
        <h1 className="text-[26px] font-semibold tracking-tight text-ink sm:text-[30px]">
          Annuaire
        </h1>
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
        availableFilieres={availableFilieres}
        selectedFilieres={selectedFilieres}
        onToggleFiliere={toggleFiliere}
        onClearFilieres={clearFilieres}
        availableSchools={availableSchools}
        selectedSchools={selectedSchools}
        onToggleSchool={toggleSchool}
        onClearSchools={clearSchools}
        resultCount={filtered.length}
        loading={loading}
      />

      {/* Contenu */}
      <div className="flex-1 px-4 py-5 sm:px-7 sm:py-6">
        {view === 'grid' && <GridView persons={filtered} loading={loading} />}
        {view === 'list' && <ListView persons={filtered} loading={loading} />}
        {view === 'timeline' && <TimelineView persons={filtered} loading={loading} />}
        {view === 'tree' && (
          <TreeView persons={filtered} links={links} loading={loading || linksLoading} />
        )}
        {view === 'ego' && (
          <EgoGraphView persons={persons} links={links} loading={loading || linksLoading} />
        )}
      </div>
    </div>
  );
}
