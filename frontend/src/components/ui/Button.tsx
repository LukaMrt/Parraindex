import { cva, type VariantProps } from 'class-variance-authority';
import type { ButtonHTMLAttributes } from 'react';
import { cn } from '../../lib/cn';

const buttonVariants = cva(
  'inline-flex cursor-pointer items-center justify-center gap-2 font-medium transition-all select-none disabled:opacity-40 disabled:cursor-not-allowed',
  {
    variants: {
      variant: {
        primary: 'bg-ink text-white border-transparent hover:-translate-y-0.5 hover:opacity-90',
        secondary: 'border border-line bg-surface text-ink hover:border-ink hover:bg-bg',
        ghost: 'bg-transparent border-transparent text-ink-3 hover:bg-bg hover:text-ink',
        danger: 'bg-danger text-white border-transparent hover:opacity-90',
        'pill-neutral': 'rounded-full border border-line bg-surface text-ink-2 hover:border-ink',
        'pill-active': 'rounded-full border border-ink bg-ink text-white',
        'pill-color': 'rounded-full border text-white',
      },
      size: {
        sm: 'h-7  px-3   text-xs  rounded-md',
        md: 'h-9  px-4   text-sm  rounded-[9px]',
        lg: 'h-11 px-5   text-sm  rounded-[10px]',
        icon: 'h-8  w-8    text-sm  rounded-md p-0',
      },
    },
    defaultVariants: {
      variant: 'primary',
      size: 'md',
    },
  },
);

export type ButtonVariant = NonNullable<VariantProps<typeof buttonVariants>['variant']>;
export type ButtonSize = NonNullable<VariantProps<typeof buttonVariants>['size']>;

interface ButtonProps
  extends ButtonHTMLAttributes<HTMLButtonElement>, VariantProps<typeof buttonVariants> {
  /** Couleur personnalisée pour pill-color (background + border) */
  accentColor?: string;
}

export function Button({
  variant,
  size,
  accentColor,
  className,
  style,
  children,
  ...props
}: ButtonProps) {
  const colorStyle =
    variant === 'pill-color' && accentColor
      ? { backgroundColor: accentColor, borderColor: accentColor, ...style }
      : style;

  return (
    <button
      className={cn(buttonVariants({ variant, size }), className)}
      style={colorStyle}
      {...props}
    >
      {children}
    </button>
  );
}
