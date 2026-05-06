import type { ReactNode } from 'react';
import { cn } from '../../lib/cn';

interface Tab<T extends string> {
  value: T;
  label: string;
  icon?: ReactNode;
}

interface TabSwitcherProps<T extends string> {
  tabs: Tab<T>[];
  value: T;
  onChange: (value: T) => void;
  className?: string;
}

export function TabSwitcher<T extends string>({
  tabs,
  value,
  onChange,
  className,
}: TabSwitcherProps<T>) {
  return (
    <div
      className={cn('flex gap-0.5 rounded-[9px] border border-line bg-surface p-[3px]', className)}
    >
      {tabs.map((tab) => (
        <button
          key={tab.value}
          onClick={() => {
            onChange(tab.value);
          }}
          className={cn(
            'flex cursor-pointer items-center gap-1.5 rounded-[7px] border-none px-3 py-[7px] text-xs font-medium transition-all',
            value === tab.value ? 'bg-ink text-white' : 'bg-transparent text-ink-2 hover:text-ink',
          )}
        >
          {tab.icon}
          {tab.label}
        </button>
      ))}
    </div>
  );
}
