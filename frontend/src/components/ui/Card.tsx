import { cva, type VariantProps } from 'class-variance-authority';
import { type HTMLAttributes, type MouseEvent } from 'react';
import { cn } from '../../lib/cn';

const cardVariants = cva('border border-line bg-surface transition-all', {
  variants: {
    radius: {
      lg: 'rounded-lg' /* 12px — stat cards */,
      xl: 'rounded-xl' /* 14px — cartes génériques */,
      '2xl': 'rounded-2xl' /* 16px — hero cards */,
    },
    padding: {
      none: '',
      sm: 'p-4',
      md: 'p-6',
      lg: 'p-8',
    },
    hoverable: {
      true: 'cursor-pointer hover:-translate-y-0.5 hover:shadow-md',
      false: '',
    },
  },
  defaultVariants: {
    radius: 'xl',
    padding: 'md',
    hoverable: false,
  },
});

interface CardProps extends HTMLAttributes<HTMLDivElement>, VariantProps<typeof cardVariants> {
  /** Couleur de la bordure au hover */
  hoverColor?: string;
}

export function Card({
  radius,
  padding,
  hoverable,
  hoverColor,
  className,
  onMouseEnter,
  onMouseLeave,
  ...props
}: CardProps) {
  const handleEnter = (e: MouseEvent<HTMLDivElement>) => {
    if (hoverColor) e.currentTarget.style.borderColor = hoverColor;
    onMouseEnter?.(e);
  };
  const handleLeave = (e: MouseEvent<HTMLDivElement>) => {
    if (hoverColor) e.currentTarget.style.borderColor = '';
    onMouseLeave?.(e);
  };

  return (
    <div
      className={cn(cardVariants({ radius, padding, hoverable }), className)}
      onMouseEnter={handleEnter}
      onMouseLeave={handleLeave}
      {...props}
    />
  );
}
