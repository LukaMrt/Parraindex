import { cva, type VariantProps } from 'class-variance-authority';
import type { HTMLAttributes } from 'react';
import { cn } from '../../lib/cn';

const badgeVariants = cva('inline-flex items-center gap-1.5 font-medium', {
  variants: {
    variant: {
      default: 'rounded-full border border-line bg-bg text-ink-2 text-xs px-2.5 py-0.5',
      promo: 'rounded-full text-xs px-2.5 py-1',
      status: 'text-xs',
      type: 'rounded-[18px] text-xs px-3 py-1',
    },
  },
  defaultVariants: { variant: 'default' },
});

interface BadgeProps extends HTMLAttributes<'span'>, VariantProps<typeof badgeVariants> {
  /** Couleur pour variant=promo ou type */
  color?: string;
  /** Affiche un point coloré avant le texte */
  dot?: boolean;
}

export function Badge({ variant, color, dot, className, children, style, ...props }: BadgeProps) {
  const colorStyle = color
    ? {
        color,
        backgroundColor: color + '18',
        borderColor: color + '30',
        ...style,
      }
    : style;

  return (
    <span className={cn(badgeVariants({ variant }), className)} style={colorStyle} {...props}>
      {dot && (
        <span
          className="inline-block shrink-0 rounded-full"
          style={{ width: 6, height: 6, background: color ?? 'currentColor' }}
        />
      )}
      {children}
    </span>
  );
}
