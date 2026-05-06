import { useEffect, type ReactNode } from 'react';
import { cn } from '../../lib/cn';

interface ModalProps {
  open: boolean;
  onClose: () => void;
  children: ReactNode;
  maxWidth?: string;
  className?: string;
}

export function Modal({ open, onClose, children, maxWidth = '420px', className }: ModalProps) {
  useEffect(() => {
    if (!open) return;
    const handler = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        onClose();
      }
    };
    window.addEventListener('keydown', handler);
    return () => {
      window.removeEventListener('keydown', handler);
    };
  }, [open, onClose]);

  if (!open) return null;

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center p-4"
      style={{
        background: 'rgba(20,22,28,0.45)',
        backdropFilter: 'blur(8px)',
        animation: 'fade-in 0.2s ease',
      }}
      onClick={(e) => {
        if (e.target === e.currentTarget) {
          onClose();
        }
      }}
    >
      <div
        className={cn(
          'w-full rounded-2xl border border-line bg-surface p-7 shadow-modal',
          className,
        )}
        style={{
          maxWidth,
          animation: 'modal-in 0.25s ease',
        }}
      >
        {children}
      </div>
    </div>
  );
}
