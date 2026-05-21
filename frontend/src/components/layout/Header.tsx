import { useState } from 'react';
import { Link } from 'react-router';
import { Avatar } from '../ui';
import { useAuth } from '../../hooks/useAuth';

export function Header() {
  const { user, logout } = useAuth();
  const [menuOpen, setMenuOpen] = useState(false);

  function closeMenu() {
    setMenuOpen(false);
  }

  return (
    <header className="flex h-[var(--header-height)] items-center justify-between border-b border-line bg-surface px-4 sm:px-6">
      <Link to="/" className="flex items-center gap-2 font-semibold text-ink">
        <img src="/images/icons/logo-blue.svg" alt="Logo" className="h-8 w-8" />
        <span>Parraindex</span>
      </Link>

      <nav className="flex items-center gap-1 text-sm">
        {user !== null ? (
          <>
            {/* Desktop : liens plats */}
            <Link
              to={`/person/${user.person.id}`}
              className="hidden rounded-md px-3 py-1.5 text-ink-2 transition-colors hover:bg-bg hover:text-ink sm:block"
            >
              Mon compte
            </Link>
            {user.isAdmin && (
              <a
                href="/admin"
                className="hidden rounded-md px-3 py-1.5 text-ink-2 transition-colors hover:bg-bg hover:text-ink sm:block"
              >
                Administration
              </a>
            )}
            <Link to={`/person/${user.person.id}`} className="ml-2 hidden sm:block">
              <Avatar person={user.person} size={32} initialsScale={0.38} />
            </Link>
            <button
              onClick={() => {
                void logout();
              }}
              className="hidden cursor-pointer rounded-md px-3 py-1.5 text-ink-2 transition-colors hover:bg-bg hover:text-ink sm:block"
            >
              Déconnexion
            </button>

            {/* Mobile : avatar → menu déroulant */}
            <div className="relative sm:hidden">
              {menuOpen && <div className="fixed inset-0 z-40" onClick={closeMenu} />}
              <button
                onClick={() => {
                  setMenuOpen((o) => !o);
                }}
                className="ml-1 cursor-pointer rounded-full"
                aria-label="Menu utilisateur"
              >
                <Avatar person={user.person} size={32} initialsScale={0.38} />
              </button>
              {menuOpen && (
                <div className="absolute right-0 top-full z-50 mt-2 min-w-[160px] overflow-hidden rounded-xl border border-line bg-surface shadow-lg">
                  <Link
                    to={`/person/${user.person.id}`}
                    onClick={closeMenu}
                    className="block px-4 py-2.5 text-[13.5px] text-ink-2 transition-colors hover:bg-bg hover:text-ink"
                  >
                    Mon compte
                  </Link>
                  {user.isAdmin && (
                    <a
                      href="/admin"
                      onClick={closeMenu}
                      className="block px-4 py-2.5 text-[13.5px] text-ink-2 transition-colors hover:bg-bg hover:text-ink"
                    >
                      Administration
                    </a>
                  )}
                  <div className="border-t border-line" />
                  <button
                    onClick={() => {
                      closeMenu();
                      void logout();
                    }}
                    className="block w-full cursor-pointer px-4 py-2.5 text-left text-[13.5px] text-danger transition-colors hover:bg-bg"
                  >
                    Déconnexion
                  </button>
                </div>
              )}
            </div>
          </>
        ) : (
          <>
            <Link
              to="/login"
              className="rounded-md px-2.5 py-1.5 text-ink-2 transition-colors hover:bg-bg hover:text-ink sm:px-3"
            >
              Se connecter
            </Link>
            <Link
              to="/register"
              className="rounded-md bg-ink px-2.5 py-1.5 text-white transition-opacity hover:opacity-90 sm:px-3"
            >
              S&apos;inscrire
            </Link>
          </>
        )}
      </nav>
    </header>
  );
}
