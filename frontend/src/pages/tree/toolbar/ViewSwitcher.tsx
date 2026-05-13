import { cn } from '../../../lib/cn';
import { VIEW_OPTIONS, type DirectoryView } from '../types';

interface ViewSwitcherProps {
  value: DirectoryView;
  onChange: (view: DirectoryView) => void;
}

export function ViewSwitcher({ value, onChange }: ViewSwitcherProps) {
  return (
    <div className="flex gap-0.5 rounded-[9px] border border-line bg-surface p-0.5">
      {VIEW_OPTIONS.map((opt) => {
        const active = value === opt.value;
        return (
          <button
            key={opt.value}
            onClick={() => {
              onChange(opt.value);
            }}
            className={cn(
              'flex h-8 cursor-pointer items-center gap-1.5 rounded-md px-3 text-[12.5px] font-medium transition-all duration-150',
              active ? 'bg-ink text-white' : 'text-ink-2 hover:text-ink',
            )}
          >
            <svg
              width={13}
              height={13}
              viewBox="0 0 20 20"
              fill="none"
              stroke="currentColor"
              strokeWidth={1.5}
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <path d={opt.iconPath} />
            </svg>
            {opt.label}
          </button>
        );
      })}
    </div>
  );
}
