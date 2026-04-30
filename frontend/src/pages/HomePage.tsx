import { Link } from 'react-router';
import { useAuth } from '../hooks/useAuth';

export function HomePage() {
  const { user } = useAuth();

  return (
    <div className="flex min-h-[calc(100vh-3.5rem)] flex-col items-center justify-center gap-8 text-center">
      <img src="/images/icons/logo-blue.svg" alt="Logo Parraindex" className="h-24 w-24" />

      <Link to="/tree" className="group">
        <h1 className="text-4xl font-bold text-dark-blue">
          <span className="font-normal text-medium-blue">Le</span> Parraindex
        </h1>
        <p className="mt-2 text-medium-blue transition-colors group-hover:text-light-blue">
          {"L'annuaire des parrains de l'IUT Lyon 1"}
        </p>
      </Link>

      <nav className="flex gap-6 text-dark-blue">
        <Link to="/tree" className="transition-colors hover:text-light-blue">
          {'Découvrir ma famille'}
        </Link>
        <Link to="/contact" className="transition-colors hover:text-light-blue">
          Contact
        </Link>
        <Link to="/about" className="transition-colors hover:text-light-blue">
          {'À propos du projet'}
        </Link>
      </nav>

      {user === null && (
        <div className="flex gap-4">
          <Link
            to="/login"
            className="rounded bg-dark-blue px-6 py-2 text-white transition-colors hover:bg-medium-blue"
          >
            Se connecter
          </Link>
          <Link
            to="/register"
            className="rounded border border-dark-blue px-6 py-2 text-dark-blue transition-colors hover:bg-dark-blue hover:text-white"
          >
            {"S'inscrire"}
          </Link>
        </div>
      )}
    </div>
  );
}
