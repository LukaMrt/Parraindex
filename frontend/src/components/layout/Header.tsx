import { Link } from 'react-router';
import { useAuth } from '../../hooks/useAuth';
import { pictureUrl } from '../../lib/imageUrl';

export function Header() {
  const { user, logout } = useAuth();

  return (
    <header className="flex h-[var(--header-height)] items-center justify-between border-b border-line bg-surface px-6">
      <Link to="/" className="flex items-center gap-2 font-semibold text-ink">
        <img src="/images/icons/logo-blue.svg" alt="Logo" className="h-8 w-8" />
        <span>Parraindex</span>
      </Link>

      <nav className="flex items-center gap-1 text-sm">
        {user !== null ? (
          <>
            <Link
              to={`/person/${user.person.id}`}
              className="rounded-md px-3 py-1.5 text-ink-2 transition-colors hover:bg-bg hover:text-ink"
            >
              Mon compte
            </Link>
            {user.isAdmin && (
              <Link
                to="/admin/contacts"
                className="rounded-md px-3 py-1.5 text-ink-2 transition-colors hover:bg-bg hover:text-ink"
              >
                Administration
              </Link>
            )}
            <img
              src={pictureUrl(user.person.picture)}
              alt={user.person.fullName}
              className="ml-2 h-8 w-8 rounded-full object-cover"
            />
            <button
              onClick={() => {
                void logout();
              }}
              className="rounded-md px-3 cursor-pointer py-1.5 text-ink-2 transition-colors hover:bg-bg hover:text-ink"
            >
              Déconnexion
            </button>
          </>
        ) : (
          <>
            <Link
              to="/login"
              className="rounded-md px-3 py-1.5 text-ink-2 transition-colors hover:bg-bg hover:text-ink"
            >
              Se connecter
            </Link>
            <Link
              to="/register"
              className="rounded-md bg-ink px-3 py-1.5 text-white transition-opacity hover:opacity-90"
            >
              S&apos;inscrire
            </Link>
          </>
        )}
      </nav>
    </header>
  );
}
