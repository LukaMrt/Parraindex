import type { ReactNode } from 'react';
import { cn } from '../../lib/cn';

interface EmptyStateProps {
  icon?: ReactNode;
  title: string;
  description?: string;
  action?: ReactNode;
  dashed?: boolean;
  className?: string;
}

export function EmptyState({
  icon = '∅',
  title,
  description,
  action,
  dashed = false,
  className,
}: EmptyStateProps) {
  return (
    <div
      className={cn(
        'flex flex-col items-center justify-center rounded-xl bg-surface p-12 text-center',
        dashed ? 'border border-dashed border-line' : 'border border-line',
        className,
      )}
    >
      {icon && <div className="mb-2 text-4xl text-ink-4">{icon}</div>}
      <div className="text-[15px] font-medium text-ink">{title}</div>
      {description && <div className="mt-1.5 max-w-xs text-[13px] text-ink-3">{description}</div>}
      {action && <div className="mt-4">{action}</div>}
    </div>
  );
}
