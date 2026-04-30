import { useAuth } from '../hooks/useAuth';

export function HomePage() {
  const { user, logout } = useAuth();

  return (
    <div className="flex min-h-screen flex-col items-center justify-center bg-light-grey">
      <h1 className="text-3xl font-bold text-dark-blue">Parraindex</h1>
      {user !== null && (
        <p className="mt-2 text-medium-blue">Connecté en tant que {user.person.fullName}</p>
      )}
      <button
        onClick={() => {
          void logout();
        }}
        className="mt-6 rounded bg-dark-blue px-4 py-2 text-white hover:bg-medium-blue"
      >
        Déconnexion
      </button>
    </div>
  );
}
