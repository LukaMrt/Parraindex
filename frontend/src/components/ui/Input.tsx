import type { InputHTMLAttributes, ReactNode } from 'react';
import { cn } from '../../lib/cn';

interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
  /** Icône ou élément affiché à gauche dans l'input */
  leadingIcon?: ReactNode;
  wrapperClassName?: string;
}

export function Input({ leadingIcon, wrapperClassName, className, ...props }: InputProps) {
  if (leadingIcon) {
    return (
      <div className={cn('relative', wrapperClassName)}>
        <span className="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-ink-4">
          {leadingIcon}
        </span>
        <input
          className={cn(
            'h-9 w-full rounded-[9px] border border-line bg-surface pl-9 pr-3.5 text-sm text-ink outline-none transition-colors placeholder:text-ink-4 focus:border-ink-2',
            className,
          )}
          {...props}
        />
      </div>
    );
  }

  return (
    <input
      className={cn(
        'h-9 w-full rounded-[9px] border border-line bg-surface px-3.5 text-sm text-ink outline-none transition-colors placeholder:text-ink-4 focus:border-ink-2',
        className,
      )}
      {...props}
    />
  );
}
