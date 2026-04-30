import { useState } from 'react';
import { useSearchParams } from 'react-router';
import { requestPasswordReset, confirmPasswordReset } from '../../lib/api/auth';

export function ResetPasswordPage() {
  const [searchParams] = useSearchParams();
  const token = searchParams.get('token');

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [message, setMessage] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit() {
    setSubmitting(true);
    setError(null);

    if (token !== null) {
      const result = await confirmPasswordReset(token, password);
      if (result.ok) {
        setMessage('Mot de passe modifié. Vous pouvez vous connecter.');
      } else {
        setError(result.error.message);
      }
    } else {
      const result = await requestPasswordReset(email);
      if (result.ok) {
        setMessage('Si un compte existe, un email de réinitialisation a été envoyé.');
      } else {
        setError(result.error.message);
      }
    }

    setSubmitting(false);
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-light-grey">
      <div className="w-full max-w-md rounded-lg bg-white p-8 shadow">
        <h1 className="mb-6 text-2xl font-bold text-dark-blue">
          {token !== null ? 'Nouveau mot de passe' : 'Réinitialiser le mot de passe'}
        </h1>

        {message !== null && (
          <p className="mb-4 rounded bg-light-green p-3 text-dark-green">{message}</p>
        )}
        {error !== null && <p className="mb-4 rounded bg-light-red p-3 text-dark-red">{error}</p>}

        <form
          onSubmit={(e) => {
            e.preventDefault();
            void handleSubmit();
          }}
          className="flex flex-col gap-4"
        >
          {token !== null ? (
            <input
              type="password"
              placeholder="Nouveau mot de passe"
              value={password}
              onChange={(e) => {
                setPassword(e.target.value);
              }}
              className="rounded border border-medium-grey px-3 py-2"
              required
            />
          ) : (
            <input
              type="email"
              placeholder="Email universitaire"
              value={email}
              onChange={(e) => {
                setEmail(e.target.value);
              }}
              className="rounded border border-medium-grey px-3 py-2"
              required
            />
          )}
          <button
            type="submit"
            disabled={submitting}
            className="rounded bg-dark-blue py-2 font-medium text-white disabled:opacity-50"
          >
            {submitting ? 'Envoi…' : 'Envoyer'}
          </button>
        </form>
      </div>
    </div>
  );
}
