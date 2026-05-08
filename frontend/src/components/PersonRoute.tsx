import { Navigate, Outlet, useParams } from 'react-router';
import { useAuth } from '../hooks/useAuth';

export function PersonRoute() {
  const { user, isLoading } = useAuth();
  const { id } = useParams<{ id: string }>();

  if (isLoading) return null;
  if (!user) return <Navigate to="/login" replace />;

  const personId = Number(id);
  if (!user.isAdmin && user.person.id !== personId) {
    return <Navigate to={`/person/${id}`} replace />;
  }

  return <Outlet />;
}
