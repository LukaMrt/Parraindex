import { Navigate, Outlet } from 'react-router';
import { useAuth } from '../hooks/useAuth';

export function AdminRoute() {
  const { user, isLoading } = useAuth();

  if (isLoading) return null;
  if (!user) return <Navigate to="/login" replace />;
  if (!user.isAdmin) return <Navigate to="/" replace />;

  return <Outlet />;
}
