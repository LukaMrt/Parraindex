import { useEffect, useState } from 'react';
import { cn } from '../../lib/cn';
import type { Notification, NotificationType } from '../../context/NotificationContext';

const CONFIG: Record<NotificationType, { accentClass: string }> = {
  success: { accentClass: 'bg-success' },
  error: { accentClass: 'bg-danger' },
  warning: { accentClass: 'bg-warning' },
  info: { accentClass: 'bg-ink-3' },
};

interface ToastProps {
  notification: Notification;
  onDismiss: (id: string) => void;
}

export function Toast({ notification, onDismiss }: ToastProps) {
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    // Déclenche l'entrée après le premier rendu pour activer la transition CSS
    const raf = requestAnimationFrame(() => {
      setVisible(true);
    });
    return () => {
      cancelAnimationFrame(raf);
    };
  }, []);

  const { accentClass } = CONFIG[notification.type];

  return (
    <div
      role="alert"
      className={cn(
        'flex items-center overflow-hidden rounded-xl bg-surface border border-line shadow-md text-[13px]',
        'transition-all duration-200',
        visible ? 'translate-x-0 opacity-100' : 'translate-x-4 opacity-0',
      )}
    >
      <span className={cn('self-stretch w-2 shrink-0', accentClass)} />
      <span className="flex-1 text-ink px-4 py-3">{notification.message}</span>
      <button
        onClick={() => {
          onDismiss(notification.id);
        }}
        className="mr-3 shrink-0 text-ink-4 hover:text-ink transition-colors cursor-pointer"
        aria-label="Fermer"
      >
        ✕
      </button>
    </div>
  );
}

interface ToastContainerProps {
  notifications: Notification[];
  onDismiss: (id: string) => void;
}

export function ToastContainer({ notifications, onDismiss }: ToastContainerProps) {
  if (notifications.length === 0) return null;

  return (
    <div className="fixed top-[calc(var(--header-height)+0.5rem)] right-4 z-50 flex flex-col gap-2 w-80 max-w-[calc(100vw-2rem)]">
      {notifications.map((n) => (
        <Toast key={n.id} notification={n} onDismiss={onDismiss} />
      ))}
    </div>
  );
}
