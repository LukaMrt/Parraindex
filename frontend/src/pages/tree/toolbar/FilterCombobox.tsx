import { useEffect, useRef, useState } from 'react';
import { cn } from '../../../lib/cn';

const MAX_CHIPS = 2;

interface FilterComboboxProps {
  label: string;
  items: string[];
  selected: string[];
  onToggle: (item: string) => void;
  onClear: () => void;
  formatItem?: (item: string) => string;
}

export function FilterCombobox({
  label,
  items,
  selected,
  onToggle,
  onClear,
  formatItem,
}: FilterComboboxProps) {
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState('');
  const ref = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLInputElement>(null);

  const fmt = formatItem ?? ((s) => s);
  const normalize = (s: string) => s.toLowerCase().normalize('NFD').replace(/\p{M}/gu, '');

  const filtered = query.trim()
    ? items.filter((item) => normalize(fmt(item)).includes(normalize(query)))
    : items;

  useEffect(() => {
    if (!open) return;
    inputRef.current?.focus();

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
      setQuery('');
    };
  }, [open]);

  if (items.length === 0) return null;

  const active = selected.length > 0;
  const compact = selected.length > MAX_CHIPS;

  return (
    <div ref={ref} className="relative w-44 shrink-0">
      {/* Trigger — largeur fixe */}
      <div
        onClick={() => {
          setOpen(true);
        }}
        className={cn(
          'flex h-9 w-full cursor-text items-center gap-1 overflow-hidden rounded-[9px] border px-2 transition-all duration-150',
          open
            ? 'border-ink-2 bg-surface'
            : active
              ? 'border-ink bg-surface'
              : 'border-line bg-surface hover:border-ink-3',
        )}
      >
        {compact ? (
          /* Mode compact : label · N */
          <>
            <span className="flex-1 truncate text-[13px] font-medium text-ink">
              {label} · {selected.length}
            </span>
            <button
              type="button"
              onClick={(e) => {
                e.stopPropagation();
                onClear();
              }}
              className="shrink-0 cursor-pointer text-ink-4 hover:text-ink-2"
              title="Tout effacer"
            >
              <svg
                width="11"
                height="11"
                viewBox="0 0 12 12"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
              >
                <path d="M2 2l8 8M10 2l-8 8" />
              </svg>
            </button>
          </>
        ) : active ? (
          /* 1 ou 2 chips */
          <>
            {selected.map((item) => (
              <span
                key={item}
                className="flex shrink-0 items-center gap-0.5 rounded-[5px] bg-ink px-1.5 py-0.5 text-[11px] font-medium text-white"
              >
                <span className="max-w-[52px] truncate">{fmt(item)}</span>
                <button
                  type="button"
                  onClick={(e) => {
                    e.stopPropagation();
                    onToggle(item);
                  }}
                  className="cursor-pointer opacity-70 hover:opacity-100"
                >
                  <svg
                    width="8"
                    height="8"
                    viewBox="0 0 10 10"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2.2"
                    strokeLinecap="round"
                  >
                    <path d="M2 2l6 6M8 2l-6 6" />
                  </svg>
                </button>
              </span>
            ))}
          </>
        ) : (
          /* Placeholder / input de recherche */
          <input
            ref={inputRef}
            value={query}
            onChange={(e) => {
              setQuery(e.target.value);
              setOpen(true);
            }}
            onFocus={() => {
              setOpen(true);
            }}
            placeholder={label}
            className="w-full bg-transparent text-[13px] text-ink placeholder:text-ink-4 outline-none"
          />
        )}
      </div>

      {/* Dropdown */}
      {open && (
        <div className="absolute left-0 top-full z-50 mt-1.5 w-full rounded-xl border border-line bg-surface p-1.5 shadow-lg">
          {/* Input de recherche quand des chips sont affichées */}
          {active && (
            <div className="mb-1 px-1">
              <input
                ref={inputRef}
                value={query}
                onChange={(e) => {
                  setQuery(e.target.value);
                }}
                placeholder="Rechercher…"
                className="w-full rounded-[7px] border border-line bg-bg px-2.5 py-1.5 text-[12.5px] text-ink placeholder:text-ink-4 outline-none"
              />
            </div>
          )}

          <div className="max-h-56 overflow-y-auto">
            {filtered.length === 0 ? (
              <p className="px-3 py-2 text-[12.5px] text-ink-4">Aucun résultat</p>
            ) : (
              filtered.map((item) => {
                const isSelected = selected.includes(item);
                return (
                  <button
                    key={item}
                    type="button"
                    onPointerDown={(e) => {
                      e.preventDefault();
                      onToggle(item);
                      setQuery('');
                    }}
                    className={cn(
                      'flex w-full cursor-pointer items-center justify-between gap-3 rounded-[7px] px-3 py-1.5 text-[12.5px] transition-colors',
                      isSelected ? 'bg-ink/8 font-medium text-ink' : 'text-ink-2 hover:bg-bg',
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
              })
            )}
          </div>
        </div>
      )}
    </div>
  );
}
