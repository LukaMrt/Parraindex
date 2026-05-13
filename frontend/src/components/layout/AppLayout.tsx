import { Outlet } from 'react-router';
import { ToastContainer } from '../ui/Toast';
import { useNotification } from '../../hooks/useNotification';
import { Header } from './Header';

export function AppLayout() {
  const { notifications, dismiss } = useNotification();

  return (
    <div className="min-h-screen bg-light-grey">
      <Header />
      <main>
        <Outlet />
      </main>
      <ToastContainer notifications={notifications} onDismiss={dismiss} />
    </div>
  );
}
