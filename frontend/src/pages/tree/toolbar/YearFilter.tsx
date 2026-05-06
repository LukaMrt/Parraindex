import { cn } from '../../../lib/cn';

interface YearFilterProps {
  years: number[];
  selected: number[];
  onToggle: (year: number) => void;
  onClear: () => void;
}

export function YearFilter({ years, selected, onToggle, onClear }: YearFilterProps) {
  const allSelected = selected.length === 0;

  return (
    <div className="flex flex-wrap items-center gap-1.5">
      <button
        onClick={onClear}
        className={cn(
          'h-7 cursor-pointer rounded-full border px-3 text-xs font-medium transition-all duration-100',
          allSelected
            ? 'border-ink bg-ink text-white'
            : 'border-line bg-surface text-ink-2 hover:border-ink',
        )}
      >
        Toutes
      </button>

      {years.map((year) => {
        const active = selected.includes(year);
        return (
          <button
            key={year}
            onClick={() => {
              onToggle(year);
            }}
            className={cn(
              'h-7 cursor-pointer rounded-full border px-3 text-xs font-medium transition-all duration-100',
              active
                ? 'border-ink bg-ink text-white'
                : 'border-line bg-surface text-ink-2 hover:border-ink',
            )}
          >
            {year} / {String(year + 1).slice(2)}
          </button>
        );
      })}
    </div>
  );
}
