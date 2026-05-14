import { useState } from 'react';
import { requestPasswordReset } from '../../lib/api/auth';
import { Button, Input } from '../../components/ui';

export function ResetPasswordPage() {
  const [email, setEmail] = useState('');
  const [message, setMessage] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit() {
    setSubmitting(true);
    setError(null);

    const result = await requestPasswordReset(email);
    if (result.ok) {
      setMessage(
        'Si un compte existe pour cet email, un mot de passe temporaire vous a été envoyé. Connectez-vous avec ce mot de passe et changez-le depuis votre profil.',
      );
    } else {
      setError(result.error.message);
    }

    setSubmitting(false);
  }

  return (
    <div className="mx-auto max-w-sm px-4 py-16">
      <h1 className="mb-2 text-[22px] font-semibold tracking-tight text-ink">
        Mot de passe oublié
      </h1>
      <p className="mb-6 text-[14px] text-ink-3">
        Renseignez votre email. Vous recevrez un mot de passe temporaire à utiliser pour vous
        connecter, que vous pourrez ensuite modifier depuis votre profil.
      </p>

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

      {message === null && (
        <form
          onSubmit={(e) => {
            e.preventDefault();
            void handleSubmit();
          }}
          className="flex flex-col gap-3"
        >
          <Input
            type="email"
            placeholder="Email universitaire"
            value={email}
            onChange={(e) => {
              setEmail(e.target.value);
            }}
            required
          />
          <Button type="submit" size="lg" disabled={submitting} className="mt-1 w-full">
            {submitting ? 'Envoi…' : 'Envoyer'}
          </Button>
        </form>
      )}
    </div>
  );
}
