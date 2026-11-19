import { useDeferredValue, useMemo } from 'react';
import { filterPersons } from '../../lib/persons';
import { DirectoryToolbar } from './toolbar/DirectoryToolbar';
import { usePersons } from './usePersons';
import { useSponsorsGraph } from './useSponsorsGraph';
import { useDirectoryState } from './useDirectoryState';
import { GridView } from './views/GridView';
import { ListView } from './views/ListView';
import { TimelineView } from './views/TimelineView';
import { TreeView } from './views/TreeView';
import { EgoGraphView } from './views/EgoGraphView';

export function TreePage() {
  const { persons, total, loading, loadingMore, error, retry } = usePersons();
  const links = useSponsorsGraph(persons);
  const directory = useDirectoryState();
  const { state } = directory;

  // La saisie reste fluide : le filtrage (et le re-layout des graphes) suit en différé
  const deferredQuery = useDeferredValue(state.q);

  const filtered = useMemo(
    () =>
      filterPersons(persons, {
        name: deferredQuery,
        years: state.years,
        alphabetical: state.alphabetical,
        filieres: state.filieres,
        schools: state.schools,
      }),
    [persons, deferredQuery, state.years, state.alphabetical, state.filieres, state.schools],
  );

  const availableYears = useMemo(
    () => [...new Set(persons.map((p) => p.startYear))].sort((a, b) => a - b),
    [persons],
  );

  const availableFilieres = useMemo(() => {
    const set = new Set<string>();
    for (const p of persons) for (const f of p.filieres) set.add(f.name);
    return [...set].sort((a, b) => a.localeCompare(b, 'fr'));
  }, [persons]);

  const availableSchools = useMemo(() => {
    const set = new Set<string>();
    for (const p of persons) {
      for (const f of p.filieres) if (f.schoolName) set.add(f.schoolName);
    }
    return [...set].sort((a, b) => a.localeCompare(b, 'fr'));
  }, [persons]);

  // Les graphes ont besoin de toutes les personnes pour être justes
  const graphLoading = loading || loadingMore;

  return (
    <div className="flex min-h-[calc(100vh-var(--header-height))] flex-col bg-bg">
      {/* En-tête */}
      <div className="border-b border-line bg-surface px-4 pb-5 pt-6 sm:px-7 sm:pt-7">
        <h1 className="text-[26px] font-semibold tracking-tight text-ink sm:text-[30px]">
          Annuaire
        </h1>
        <p className="mt-1 text-[14px] text-ink-3" aria-live="polite">
          {loading
            ? 'Chargement…'
            : loadingMore && total !== null
              ? `Chargement… ${String(persons.length)} / ${String(total)} étudiants`
              : `${String(persons.length)} étudiant${persons.length > 1 ? 's' : ''} · ${String(availableYears.length)} promotion${availableYears.length > 1 ? 's' : ''}`}
        </p>
        {loadingMore && total !== null && total > 0 && (
          <div
            className="mt-2 h-1 w-full max-w-xs overflow-hidden rounded-full bg-line"
            role="progressbar"
            aria-valuemin={0}
            aria-valuemax={total}
            aria-valuenow={persons.length}
          >
            <div
              className="h-full rounded-full bg-ink transition-all duration-300"
              style={{ width: `${String(Math.round((persons.length / total) * 100))}%` }}
            />
          </div>
        )}
      </div>

      <DirectoryToolbar
        search={state.q}
        onSearchChange={directory.setName}
        view={state.view}
        onViewChange={directory.setView}
        alphabetical={state.alphabetical}
        onToggleAlphabetical={directory.toggleAlphabetical}
        years={availableYears}
        selectedYears={state.years}
        onToggleYear={directory.toggleYear}
        onClearYears={directory.clearYears}
        availableFilieres={availableFilieres}
        selectedFilieres={state.filieres}
        onToggleFiliere={directory.toggleFiliere}
        onClearFilieres={directory.clearFilieres}
        availableSchools={availableSchools}
        selectedSchools={state.schools}
        onToggleSchool={directory.toggleSchool}
        onClearSchools={directory.clearSchools}
        resultCount={filtered.length}
        loading={loading}
        filtersPartial={loadingMore}
        filtersDisabled={state.view === 'ego'}
      />

      {/* Contenu */}
      <div className="flex-1 px-4 py-5 sm:px-7 sm:py-6">
        {error && (
          <div
            role="alert"
            className="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-danger/20 bg-danger/10 px-4 py-3 text-[13px] text-danger"
          >
            <span>
              {persons.length > 0
                ? `Une partie de l'annuaire n'a pas pu être chargée (${error})`
                : error}
            </span>
            <button
              type="button"
              onClick={retry}
              className="rounded-lg border border-danger/30 bg-surface px-3 py-1 font-medium"
            >
              Réessayer
            </button>
          </div>
        )}

        {state.view === 'grid' && <GridView persons={filtered} loading={loading} />}
        {state.view === 'list' && <ListView persons={filtered} loading={loading} />}
        {state.view === 'timeline' && <TimelineView persons={filtered} loading={loading} />}
        {state.view === 'tree' && (
          <TreeView persons={filtered} links={links} loading={graphLoading} />
        )}
        {state.view === 'ego' && (
          <EgoGraphView
            persons={persons}
            links={links}
            loading={graphLoading}
            focusId={state.focus}
            onFocusChange={directory.setFocus}
          />
        )}
      </div>
    </div>
  );
}
