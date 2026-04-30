import { Link } from 'react-router';
import { useAuth } from '../../hooks/useAuth';
import { pictureUrl } from '../../lib/imageUrl';

export function Header() {
  const { user, logout } = useAuth();

  return (
    <header className="flex items-center justify-between bg-white px-6 py-3 shadow-sm">
      <Link to="/" className="flex items-center gap-2 font-semibold text-dark-blue">
        <img src="/images/icons/logo-blue.svg" alt="Logo" className="h-8 w-8" />
        <span>Parraindex</span>
      </Link>

      <nav className="flex items-center gap-4 text-sm">
        {user !== null ? (
          <>
            <Link
              to={`/personne/${user.person.id}`}
              className="text-dark-blue hover:text-light-blue"
            >
              Mon compte
            </Link>
            <Link
              to={`/personne/${user.person.id}/modifier`}
              className="text-dark-blue hover:text-light-blue"
            >
              Modifier
            </Link>
            {user.isAdmin && (
              <Link to="/admin/contacts" className="text-dark-blue hover:text-light-blue">
                Administration
              </Link>
            )}
            <img
              src={pictureUrl(user.person.picture)}
              alt={user.person.fullName}
              className="h-8 w-8 rounded-full object-cover"
            />
            <button
              onClick={() => {
                void logout();
              }}
              className="text-dark-blue hover:text-light-blue"
            >
              {'Déconnexion'}
            </button>
          </>
        ) : (
          <>
            <Link to="/login" className="text-dark-blue hover:text-light-blue">
              Se connecter
            </Link>
            <Link to="/register" className="text-dark-blue hover:text-light-blue">
              {"S'inscrire"}
            </Link>
          </>
        )}
      </nav>
    </header>
  );
}
