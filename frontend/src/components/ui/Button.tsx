import { cva, type VariantProps } from 'class-variance-authority';
import { useEffect, useRef, useState } from 'react';
import type { ButtonHTMLAttributes, MouseEvent, ReactNode } from 'react';
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
  /** Active la double confirmation : le premier clic affiche "Confirmer" pendant 5s */
  confirm?: boolean;
  /** Variant appliqué pendant l'état de confirmation */
  confirmVariant?: ButtonVariant;
  /** Affiche un spinner et désactive le bouton */
  loading?: boolean;
  /** Icône affichée à gauche du texte (ou seule si pas de children) */
  icon?: ReactNode;
}

function Spinner() {
  return (
    <svg
      width="14"
      height="14"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2.5"
      strokeLinecap="round"
      className="animate-spin"
    >
      <path d="M21 12a9 9 0 1 1-6.219-8.56" />
    </svg>
  );
}

export function Button({
  variant,
  size,
  accentColor,
  confirm,
  confirmVariant,
  loading,
  icon,
  className,
  style,
  onClick,
  children,
  disabled,
  ...props
}: ButtonProps) {
  const [confirming, setConfirming] = useState(false);
  const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(
    () => () => {
      if (timerRef.current) clearTimeout(timerRef.current);
    },
    [],
  );

  const colorStyle =
    variant === 'pill-color' && accentColor
      ? { backgroundColor: accentColor, borderColor: accentColor, ...style }
      : style;

  function handleClick(e: MouseEvent<HTMLButtonElement>) {
    if (!confirm) {
      onClick?.(e);
      return;
    }
    if (!confirming) {
      setConfirming(true);
      timerRef.current = setTimeout(() => {
        setConfirming(false);
      }, 5000);
      return;
    }
    if (timerRef.current) clearTimeout(timerRef.current);
    setConfirming(false);
    onClick?.(e);
  }

  const showConfirm = confirm && confirming;
  const activeVariant = showConfirm && confirmVariant ? confirmVariant : variant;

  return (
    <button
      className={cn(buttonVariants({ variant: activeVariant, size }), className)}
      style={colorStyle}
      onClick={handleClick}
      disabled={disabled ?? loading}
      {...props}
    >
      {loading ? <Spinner /> : icon && !showConfirm ? icon : null}
      {showConfirm ? 'Confirmer' : children}
    </button>
  );
}
