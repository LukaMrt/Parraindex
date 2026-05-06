import { Input } from '../../../components/ui';
import { cn } from '../../../lib/cn';
import type { DirectoryView } from '../types';
import { ViewSwitcher } from './ViewSwitcher';
import { YearFilter } from './YearFilter';

const SearchIcon = () => (
  <svg width={14} height={14} viewBox="0 0 15 15" fill="currentColor">
    <path
      fillRule="evenodd"
      d="M10 6.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0ZM9.38 10.44a5 5 0 1 1 1.06-1.06l3.07 3.07a.75.75 0 1 1-1.06 1.06L9.38 10.44Z"
    />
  </svg>
);

interface DirectoryToolbarProps {
  search: string;
  onSearchChange: (value: string) => void;
  view: DirectoryView;
  onViewChange: (view: DirectoryView) => void;
  alphabetical: boolean;
  onToggleAlphabetical: () => void;
  years: number[];
  selectedYears: number[];
  onToggleYear: (year: number) => void;
  onClearYears: () => void;
  resultCount: number;
  loading: boolean;
}

export function DirectoryToolbar({
  search,
  onSearchChange,
  view,
  onViewChange,
  alphabetical,
  onToggleAlphabetical,
  years,
  selectedYears,
  onToggleYear,
  onClearYears,
  resultCount,
  loading,
}: DirectoryToolbarProps) {
  return (
    <div className="border-b border-line bg-surface px-7 py-5">
      {/* Ligne 1 : recherche + sélecteurs */}
      <div className="flex flex-wrap items-center gap-2.5">
        <Input
          leadingIcon={<SearchIcon />}
          placeholder="Rechercher…"
          value={search}
          onChange={(e) => {
            onSearchChange(e.target.value);
          }}
          wrapperClassName="flex-1 min-w-[200px]"
          disabled={loading}
        />

        <ViewSwitcher value={view} onChange={onViewChange} />

        <button
          onClick={onToggleAlphabetical}
          disabled={loading}
          className={cn(
            'flex h-9 cursor-pointer items-center gap-1.5 rounded-[9px] border px-3.5 text-[13px] font-medium transition-all duration-150',
            alphabetical
              ? 'border-transparent bg-ink text-white'
              : 'border-line bg-surface text-ink-2 hover:border-ink',
          )}
        >
          <svg
            width={13}
            height={13}
            viewBox="0 0 13 13"
            fill="none"
            stroke="currentColor"
            strokeWidth={1.5}
            strokeLinecap="round"
          >
            <path d="M1 3h11M1 6.5h7M1 10h4" />
          </svg>
          A → Z
        </button>
      </div>

      {/* Ligne 2 : filtre années + compteur */}
      <div className="mt-3.5 flex flex-wrap items-center justify-between gap-3">
        {loading ? (
          <div className="h-7 w-64 animate-pulse rounded-full bg-line" />
        ) : (
          <YearFilter
            years={years}
            selected={selectedYears}
            onToggle={onToggleYear}
            onClear={onClearYears}
          />
        )}

        {!loading && (
          <span className="shrink-0 text-[12.5px] font-medium text-ink-3">
            {resultCount} résultat{resultCount > 1 ? 's' : ''}
          </span>
        )}
      </div>
    </div>
  );
}
