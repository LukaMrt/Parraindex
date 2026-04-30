import { useState } from 'react';
import { useNavigate, Link } from 'react-router';
import { register } from '../../lib/api/auth';

export function RegisterPage() {
  const navigate = useNavigate();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit() {
    setSubmitting(true);
    setError(null);

    // TODO: implémenter la logique d'inscription
    // Appelle register({ email, password })
    // En cas de succès (201), navigue vers '/check-email'
    // En cas d'erreur VALIDATION_ERROR, afficher les violations champ par champ
    const result = await register({ email, password });
    if (result.ok) {
      void navigate('/check-email');
    } else {
      setError(result.error.message);
    }

    setSubmitting(false);
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-light-grey">
      <div className="w-full max-w-md rounded-lg bg-white p-8 shadow">
        <h1 className="mb-6 text-2xl font-bold text-dark-blue">Créer un compte</h1>

        {error !== null && <p className="mb-4 rounded bg-light-red p-3 text-dark-red">{error}</p>}

        <form
          onSubmit={(e) => {
            e.preventDefault();
            void handleSubmit();
          }}
          className="flex flex-col gap-4"
        >
          <div>
            <input
              type="email"
              placeholder="prenom.nom@etu.univ-lyon1.fr"
              value={email}
              onChange={(e) => {
                setEmail(e.target.value);
              }}
              className="w-full rounded border border-medium-grey px-3 py-2 focus:border-light-blue focus:outline-none"
              required
            />
            <p className="mt-1 text-xs text-dark-grey">Email universitaire obligatoire</p>
          </div>
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
            {submitting ? 'Inscription…' : "S'inscrire"}
          </button>
        </form>

        <p className="mt-4 text-center text-sm text-medium-blue">
          Déjà un compte ?{' '}
          <Link to="/login" className="hover:text-dark-blue">
            Se connecter
          </Link>
        </p>
      </div>
    </div>
  );
}
