import { useEffect, useRef, useState } from 'react';
import type { InputHTMLAttributes, KeyboardEvent, ReactNode } from 'react';
import { cn } from '../../lib/cn';

type BaseProps = Omit<InputHTMLAttributes<HTMLInputElement>, 'value' | 'onChange'> & {
  value: string;
  onChange: (value: string) => void;
  wrapperClassName?: string;
};

type SyncProps = BaseProps & {
  suggestions: string[];
  search?: never;
  getLabel?: never;
  getKey?: never;
  renderItem?: never;
  onPick?: (item: string) => void;
  debounceMs?: never;
  minChars?: never;
};

type AsyncProps<T> = BaseProps & {
  suggestions?: never;
  search: (query: string) => Promise<T[]>;
  getLabel: (item: T) => string;
  getKey: (item: T) => string | number;
  renderItem?: (item: T, active: boolean) => ReactNode;
  onPick: (item: T) => void;
  debounceMs?: number;
  minChars?: number;
};

export type SuggestInputProps<T = string> = SyncProps | AsyncProps<T>;

export function SuggestInput<T = string>(props: SuggestInputProps<T>) {
  if ('search' in props && typeof props.search === 'function') {
    return <AsyncSuggestInput {...props} />;
  }
  return <SyncSuggestInput {...props} />;
}

// ── Mode synchrone (rétrocompat : filières, écoles…) ─────────────────────────

function SyncSuggestInput({
  value,
  onChange,
  suggestions,
  wrapperClassName,
  className,
  onFocus,
  onBlur,
  onPick,
  ...rest
}: SyncProps) {
  const [open, setOpen] = useState(false);
  const [cursor, setCursor] = useState(0);
  const wrapperRef = useRef<HTMLDivElement>(null);

  const filtered = value.trim()
    ? suggestions
        .filter((s) => s.toLowerCase().includes(value.toLowerCase()) && s !== value)
        .slice(0, 8)
    : [];

  function pick(s: string) {
    onChange(s);
    onPick?.(s);
    setOpen(false);
    setCursor(0);
  }

  function handleKeyDown(e: KeyboardEvent) {
    if (!open || filtered.length === 0) return;
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      setCursor((c) => Math.min(c + 1, filtered.length - 1));
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      setCursor((c) => Math.max(c - 1, 0));
    } else if (e.key === 'Enter') {
      e.preventDefault();
      const s = filtered[cursor];
      if (s) pick(s);
    } else if (e.key === 'Escape') {
      setOpen(false);
    }
  }

  return (
    <div ref={wrapperRef} className={cn('relative', wrapperClassName)}>
      <input
        value={value}
        onChange={(e) => {
          onChange(e.target.value);
          setCursor(0);
          setOpen(true);
        }}
        onFocus={(e) => {
          setOpen(true);
          onFocus?.(e);
        }}
        onBlur={(e) => {
          setOpen(false);
          onBlur?.(e);
        }}
        onKeyDown={handleKeyDown}
        autoComplete="off"
        className={cn(
          'h-9 w-full rounded-[9px] border border-line bg-surface px-3.5 text-sm text-ink outline-none transition-colors placeholder:text-ink-4 focus:border-ink-2',
          className,
        )}
        {...rest}
      />
      {open && filtered.length > 0 && (
        <ul className="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-xl border border-line bg-surface shadow-lg">
          {filtered.map((s, i) => (
            <li
              key={s}
              onPointerDown={(e) => {
                e.preventDefault();
                pick(s);
              }}
              className={cn(
                'cursor-pointer select-none px-3.5 py-2 text-[13px] transition-colors',
                i === cursor ? 'bg-bg text-ink' : 'text-ink-2 hover:bg-bg',
              )}
            >
              {s}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}

// ── Mode asynchrone (recherche serveur avec debounce) ────────────────────────

function AsyncSuggestInput<T>({
  value,
  onChange,
  search,
  getLabel,
  getKey,
  renderItem,
  onPick,
  debounceMs = 200,
  minChars = 2,
  wrapperClassName,
  className,
  onFocus,
  onBlur,
  ...rest
}: AsyncProps<T>) {
  const [open, setOpen] = useState(false);
  const [cursor, setCursor] = useState(0);
  const [fetched, setFetched] = useState<T[]>([]);
  const [loading, setLoading] = useState(false);
  const wrapperRef = useRef<HTMLDivElement>(null);
  const requestIdRef = useRef(0);

  const trimmed = value.trim();
  const tooShort = trimmed.length < minChars;
  const items = tooShort ? [] : fetched;

  useEffect(() => {
    if (tooShort) return;

    const id = ++requestIdRef.current;
    const timer = window.setTimeout(() => {
      setLoading(true);
      search(trimmed)
        .then((result) => {
          if (id !== requestIdRef.current) return;
          setFetched(result);
          setCursor(0);
        })
        .catch(() => {
          if (id !== requestIdRef.current) return;
          setFetched([]);
        })
        .finally(() => {
          if (id === requestIdRef.current) setLoading(false);
        });
    }, debounceMs);

    return () => {
      window.clearTimeout(timer);
    };
  }, [trimmed, tooShort, debounceMs, search]);

  function pick(item: T) {
    onChange(getLabel(item));
    onPick(item);
    setOpen(false);
    setCursor(0);
  }

  function handleKeyDown(e: KeyboardEvent) {
    if (!open || items.length === 0) return;
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      setCursor((c) => Math.min(c + 1, items.length - 1));
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      setCursor((c) => Math.max(c - 1, 0));
    } else if (e.key === 'Enter') {
      e.preventDefault();
      const item = items[cursor];
      if (item) pick(item);
    } else if (e.key === 'Escape') {
      setOpen(false);
    }
  }

  const showDropdown = open && !tooShort;
  const showEmpty = showDropdown && !loading && items.length === 0;

  return (
    <div ref={wrapperRef} className={cn('relative', wrapperClassName)}>
      <input
        value={value}
        onChange={(e) => {
          onChange(e.target.value);
          setCursor(0);
          setOpen(true);
        }}
        onFocus={(e) => {
          setOpen(true);
          onFocus?.(e);
        }}
        onBlur={(e) => {
          setOpen(false);
          onBlur?.(e);
        }}
        onKeyDown={handleKeyDown}
        autoComplete="off"
        className={cn(
          'h-9 w-full rounded-[9px] border border-line bg-surface px-3.5 text-sm text-ink outline-none transition-colors placeholder:text-ink-4 focus:border-ink-2',
          className,
        )}
        {...rest}
      />
      {showDropdown && (loading || items.length > 0 || showEmpty) && (
        <ul className="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-xl border border-line bg-surface shadow-lg">
          {loading && items.length === 0 && (
            <li className="px-3.5 py-2 text-[13px] text-ink-4">Recherche…</li>
          )}
          {!loading && showEmpty && (
            <li className="px-3.5 py-2 text-[13px] text-ink-4">Aucun résultat</li>
          )}
          {items.map((item, i) => {
            const active = i === cursor;
            return (
              <li
                key={getKey(item)}
                onPointerDown={(e) => {
                  e.preventDefault();
                  pick(item);
                }}
                className={cn(
                  'cursor-pointer select-none px-3.5 py-2 text-[13px] transition-colors',
                  active ? 'bg-bg text-ink' : 'text-ink-2 hover:bg-bg',
                )}
              >
                {renderItem ? renderItem(item, active) : getLabel(item)}
              </li>
            );
          })}
        </ul>
      )}
    </div>
  );
}
