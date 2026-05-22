import { useEffect, useRef, useState } from 'react';
import { cn } from '../../../lib/cn';

interface FilterDropdownProps {
  label: string;
  items: string[];
  selected: string[];
  onToggle: (item: string) => void;
  onClear: () => void;
  formatItem?: (item: string) => string;
}

export function FilterDropdown({
  label,
  items,
  selected,
  onToggle,
  onClear,
  formatItem,
}: FilterDropdownProps) {
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!open) return;
    function onPointerDown(e: PointerEvent) {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    }
    function onKeyDown(e: KeyboardEvent) {
      if (e.key === 'Escape') setOpen(false);
    }
    document.addEventListener('pointerdown', onPointerDown);
    document.addEventListener('keydown', onKeyDown);
    return () => {
      document.removeEventListener('pointerdown', onPointerDown);
      document.removeEventListener('keydown', onKeyDown);
    };
  }, [open]);

  if (items.length === 0) return null;

  const active = selected.length > 0;
  const fmt = formatItem ?? ((s) => s);

  return (
    <div ref={ref} className="relative">
      <button
        type="button"
        onClick={() => {
          setOpen((v) => !v);
        }}
        className={cn(
          'flex h-9 cursor-pointer items-center gap-1.5 rounded-[9px] border px-3.5 text-[13px] font-medium transition-all duration-150',
          active
            ? 'border-transparent bg-ink text-white'
            : 'border-line bg-surface text-ink-2 hover:border-ink',
        )}
      >
        {label}
        {active && (
          <span className="flex h-4 min-w-4 items-center justify-center rounded-full bg-white/20 px-1 text-[11px] font-semibold">
            {selected.length}
          </span>
        )}
        <svg
          width="10"
          height="10"
          viewBox="0 0 10 10"
          fill="none"
          stroke="currentColor"
          strokeWidth="1.8"
          strokeLinecap="round"
          strokeLinejoin="round"
          className={cn('transition-transform duration-150', open && 'rotate-180')}
        >
          <path d="M2 3.5l3 3 3-3" />
        </svg>
      </button>

      {open && (
        <div className="absolute left-0 top-full z-50 mt-1.5 min-w-[180px] rounded-xl border border-line bg-surface p-1.5 shadow-lg">
          {/* Tout sélectionner / effacer */}
          <button
            type="button"
            onClick={() => {
              onClear();
            }}
            className={cn(
              'flex w-full cursor-pointer items-center rounded-[7px] px-3 py-1.5 text-[12.5px] font-medium transition-colors',
              selected.length === 0 ? 'bg-ink text-white' : 'text-ink-2 hover:bg-bg',
            )}
          >
            Tous
          </button>

          <div className="my-1 h-px bg-line" />

          <div className="max-h-56 overflow-y-auto">
            {items.map((item) => {
              const isSelected = selected.includes(item);
              return (
                <button
                  key={item}
                  type="button"
                  onClick={() => {
                    onToggle(item);
                  }}
                  className={cn(
                    'flex w-full cursor-pointer items-center justify-between gap-3 rounded-[7px] px-3 py-1.5 text-[12.5px] transition-colors',
                    isSelected ? 'bg-ink text-white' : 'text-ink-2 hover:bg-bg',
                  )}
                >
                  <span>{fmt(item)}</span>
                  {isSelected && (
                    <svg
                      width="11"
                      height="11"
                      viewBox="0 0 12 12"
                      fill="none"
                      stroke="currentColor"
                      strokeWidth="2.2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    >
                      <path d="M2 6l3 3 5-5" />
                    </svg>
                  )}
                </button>
              );
            })}
          </div>
        </div>
      )}
    </div>
  );
}
