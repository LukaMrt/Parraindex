import { useEffect } from 'react';
import { Navigate, Outlet, useParams } from 'react-router';
import { useAuth } from '../hooks/useAuth';
import { useNotification } from '../hooks/useNotification';

export function PersonRoute() {
  const { user, isLoading } = useAuth();
  const { id } = useParams<{ id: string }>();
  const { notify } = useNotification();

  const notValidated = !isLoading && user !== null && !user.isValidated;
  const notAuthorized =
    !isLoading &&
    user !== null &&
    user.isValidated &&
    !user.isAdmin &&
    user.person.id !== Number(id);

  useEffect(() => {
    if (notValidated) {
      notify('warning', 'Votre compte est en attente de validation par un administrateur.');
    }
  }, [notValidated, notify]);

  if (isLoading) return null;
  if (!user) return <Navigate to="/login" replace />;
  if (notValidated) return <Navigate to="/" replace />;
  if (notAuthorized) return <Navigate to={`/person/${id}`} replace />;

  return <Outlet />;
}
