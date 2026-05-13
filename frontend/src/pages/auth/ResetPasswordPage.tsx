import { useState } from 'react';
import { useSearchParams } from 'react-router';
import { requestPasswordReset, confirmPasswordReset } from '../../lib/api/auth';
import { Button, Input } from '../../components/ui';

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
      const result = await requestPasswordReset(email, `${window.location.origin}/reset-password`);
      if (result.ok) {
        setMessage('Si un compte existe, un email de réinitialisation a été envoyé.');
      } else {
        setError(result.error.message);
      }
    }

    setSubmitting(false);
  }

  return (
    <div className="mx-auto max-w-sm px-4 py-16">
      <h1 className="mb-6 text-[22px] font-semibold tracking-tight text-ink">
        {token !== null ? 'Nouveau mot de passe' : 'Réinitialiser le mot de passe'}
      </h1>

      {message !== null && (
        <p className="mb-4 rounded-lg border border-success/20 bg-success/10 px-3 py-2 text-sm text-success">
          {message}
        </p>
      )}
      {error !== null && (
        <p className="mb-4 rounded-lg border border-danger/20 bg-danger/10 px-3 py-2 text-sm text-danger">
          {error}
        </p>
      )}

      <form
        onSubmit={(e) => {
          e.preventDefault();
          void handleSubmit();
        }}
        className="flex flex-col gap-3"
      >
        {token !== null ? (
          <Input
            type="password"
            placeholder="Nouveau mot de passe"
            value={password}
            onChange={(e) => {
              setPassword(e.target.value);
            }}
            required
          />
        ) : (
          <Input
            type="email"
            placeholder="Email universitaire"
            value={email}
            onChange={(e) => {
              setEmail(e.target.value);
            }}
            required
          />
        )}
        <Button type="submit" size="lg" disabled={submitting} className="mt-1 w-full">
          {submitting ? 'Envoi…' : 'Envoyer'}
        </Button>
      </form>
    </div>
  );
}
