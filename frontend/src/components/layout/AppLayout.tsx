import { Outlet } from 'react-router';
import { Header } from './Header';

export function AppLayout() {
  return (
    <div className="min-h-screen bg-light-grey">
      <Header />
      <main>
        <Outlet />
      </main>
    </div>
  );
}
