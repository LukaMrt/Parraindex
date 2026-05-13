import type { ReactNode } from 'react';
import { cn } from '../../lib/cn';

interface BreadcrumbItem {
  label: ReactNode;
  onClick?: () => void;
}

interface BreadcrumbProps {
  items: BreadcrumbItem[];
  className?: string;
}

export function Breadcrumb({ items, className }: BreadcrumbProps) {
  return (
    <nav
      className={cn('flex items-center gap-1.5 text-[13px] text-ink-3', className)}
      aria-label="Fil d'Ariane"
    >
      {items.map((item, i) => (
        <span key={i} className="flex items-center gap-1.5">
          {i > 0 && <span aria-hidden>/</span>}
          {item.onClick ? (
            <button
              onClick={item.onClick}
              className="cursor-pointer border-none bg-transparent p-0 text-ink-3 transition-colors hover:text-ink"
            >
              {item.label}
            </button>
          ) : (
            <span className="text-ink">{item.label}</span>
          )}
        </span>
      ))}
    </nav>
  );
}
