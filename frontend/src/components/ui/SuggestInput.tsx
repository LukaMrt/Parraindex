import { useEffect, useRef, useState } from 'react';
import type { InputHTMLAttributes } from 'react';
import { cn } from '../../lib/cn';

interface SuggestInputProps extends Omit<
  InputHTMLAttributes<HTMLInputElement>,
  'value' | 'onChange'
> {
  value: string;
  onChange: (value: string) => void;
  suggestions: string[];
  wrapperClassName?: string;
}

export function SuggestInput({
  value,
  onChange,
  suggestions,
  wrapperClassName,
  className,
  onFocus,
  onBlur,
  ...props
}: SuggestInputProps) {
  const [open, setOpen] = useState(false);
  const [cursor, setCursor] = useState(0);
  const wrapperRef = useRef<HTMLDivElement>(null);

  const filtered = value.trim()
    ? suggestions
        .filter((s) => s.toLowerCase().includes(value.toLowerCase()) && s !== value)
        .slice(0, 8)
    : [];

  useEffect(() => {
    setCursor(0);
  }, [value]);

  function pick(s: string) {
    onChange(s);
    setOpen(false);
  }

  function handleKeyDown(e: React.KeyboardEvent) {
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
        {...props}
      />
      {open && filtered.length > 0 && (
        <ul className="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-xl border border-line bg-surface shadow-lg">
          {filtered.map((s, i) => (
            <li
              key={s}
              // onPointerDown au lieu de onClick pour que le pick se déclenche
              // avant le onBlur de l'input, évitant la fermeture prématurée sur mobile
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
