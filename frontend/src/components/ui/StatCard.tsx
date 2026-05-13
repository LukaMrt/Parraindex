import type { HTMLAttributes } from 'react';
import { cn } from '../../lib/cn';

interface StatCardProps extends HTMLAttributes<HTMLDivElement> {
  label: string;
  value: string | number;
  accent?: string;
}

export function StatCard({ label, value, accent, className, ...props }: StatCardProps) {
  return (
    <div
      className={cn(
        'rounded-lg border border-line bg-surface px-[18px] py-4',
        props.onClick !== undefined && 'cursor-pointer',
        className,
      )}
      {...props}
    >
      <div className="text-[11px] font-semibold uppercase tracking-[0.06em] text-ink-3">
        {label}
      </div>
      <div
        className="mt-1 text-[26px] font-semibold leading-none tracking-tight"
        style={{ color: accent ?? 'var(--color-ink)' }}
      >
        {value}
      </div>
    </div>
  );
}
