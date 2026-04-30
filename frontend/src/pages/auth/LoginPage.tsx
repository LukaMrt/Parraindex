import { useState } from 'react';
import { useNavigate, Link } from 'react-router';
import { useAuth } from '../../hooks/useAuth';

export function LoginPage() {
  const { login } = useAuth();
  const navigate = useNavigate();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit() {
    setSubmitting(true);
    setError(null);

    // TODO: implémenter la logique de connexion
    // Appelle login({ email, password })
    // En cas de succès, navigue vers '/'
    // En cas d'erreur, affiche result.message dans setError
    const result = await login({ email, password });
    if (result.ok) {
      void navigate('/');
    } else {
      setError(result.message ?? 'Connexion impossible');
    }

    setSubmitting(false);
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-light-grey">
      <div className="w-full max-w-md rounded-lg bg-white p-8 shadow">
        <h1 className="mb-6 text-2xl font-bold text-dark-blue">Connexion</h1>

        {error !== null && <p className="mb-4 rounded bg-light-red p-3 text-dark-red">{error}</p>}

        <form
          onSubmit={(e) => {
            e.preventDefault();
            void handleSubmit();
          }}
          className="flex flex-col gap-4"
        >
          <input
            type="email"
            placeholder="Email universitaire"
            value={email}
            onChange={(e) => {
              setEmail(e.target.value);
            }}
            className="rounded border border-medium-grey px-3 py-2 focus:border-light-blue focus:outline-none"
            required
          />
          <input
            type="password"
            placeholder="Mot de passe"
            value={password}
            onChange={(e) => {
              setPassword(e.target.value);
            }}
            className="rounded border border-medium-grey px-3 py-2 focus:border-light-blue focus:outline-none"
            required
          />
          <button
            type="submit"
            disabled={submitting}
            className="rounded bg-dark-blue py-2 font-medium text-white disabled:opacity-50 hover:bg-medium-blue"
          >
            {submitting ? 'Connexion…' : 'Se connecter'}
          </button>
        </form>

        <div className="mt-4 flex flex-col gap-2 text-center text-sm text-medium-blue">
          <Link to="/reset-password" className="hover:text-dark-blue">
            Mot de passe oublié ?
          </Link>
          <Link to="/register" className="hover:text-dark-blue">
            Créer un compte
          </Link>
        </div>
      </div>
    </div>
  );
}
