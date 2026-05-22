import { cn } from '../../../lib/cn';

interface TagFilterProps {
  label: string;
  items: string[];
  selected: string[];
  onToggle: (item: string) => void;
  onClear: () => void;
}

export function TagFilter({ label, items, selected, onToggle, onClear }: TagFilterProps) {
  if (items.length === 0) return null;
  const allSelected = selected.length === 0;

  return (
    <div className="flex flex-wrap items-center gap-1.5">
      <span className="text-[11px] font-semibold uppercase tracking-widest text-ink-4">
        {label}
      </span>
      <button
        onClick={onClear}
        className={cn(
          'h-7 cursor-pointer rounded-full border px-3 text-xs font-medium transition-all duration-100',
          allSelected
            ? 'border-ink bg-ink text-white'
            : 'border-line bg-surface text-ink-2 hover:border-ink',
        )}
      >
        Tous
      </button>
      {items.map((item) => {
        const active = selected.includes(item);
        return (
          <button
            key={item}
            onClick={() => {
              onToggle(item);
            }}
            className={cn(
              'h-7 cursor-pointer rounded-full border px-3 text-xs font-medium transition-all duration-100',
              active
                ? 'border-ink bg-ink text-white'
                : 'border-line bg-surface text-ink-2 hover:border-ink',
            )}
          >
            {item}
          </button>
        );
      })}
    </div>
  );
}
