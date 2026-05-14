import { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router';
import { requestPasswordReset, confirmPasswordReset } from '../../lib/api/auth';
import { Button, Input } from '../../components/ui';

export function ResetPasswordPage() {
  const [searchParams] = useSearchParams();
  const token = searchParams.get('token');

  const [email, setEmail] = useState('');
  const [message, setMessage] = useState<string | null>(null);
  const [newPassword, setNewPassword] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(token !== null);

  useEffect(() => {
    if (token === null) return;

    void confirmPasswordReset(token).then((result) => {
      if (result.ok) {
        setNewPassword(result.data.password);
      } else {
        setError(result.error.message);
      }
      setSubmitting(false);
    });
  }, [token]);

  async function handleRequestReset() {
    setSubmitting(true);
    setError(null);

    const result = await requestPasswordReset(email, `${window.location.origin}/reset-password`);
    if (result.ok) {
      setMessage(
        'Si un compte existe pour cet email, un lien vous a été envoyé. Cliquez dessus pour recevoir un mot de passe temporaire.',
      );
    } else {
      setError(result.error.message);
    }

    setSubmitting(false);
  }

  if (token !== null) {
    return (
      <div className="mx-auto max-w-sm px-4 py-16">
        <h1 className="mb-6 text-[22px] font-semibold tracking-tight text-ink">
          Nouveau mot de passe
        </h1>

        {submitting && <p className="text-[14px] text-ink-3">Génération de votre mot de passe…</p>}

        {newPassword !== null && (
          <div className="rounded-xl border border-success/20 bg-success/10 p-5">
            <p className="mb-3 text-[14px] text-ink-2">Votre nouveau mot de passe temporaire :</p>
            <p className="mb-3 rounded-lg bg-surface px-4 py-3 font-mono text-[20px] font-bold tracking-widest text-ink">
              {newPassword}
            </p>
            <p className="text-[13px] text-ink-3">
              Connectez-vous avec ce mot de passe, puis changez-le depuis votre profil.
            </p>
          </div>
        )}

        {error !== null && (
          <p className="rounded-lg border border-danger/20 bg-danger/10 px-3 py-2 text-sm text-danger">
            {error}
          </p>
        )}
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-sm px-4 py-16">
      <h1 className="mb-2 text-[22px] font-semibold tracking-tight text-ink">
        Mot de passe oublié
      </h1>
      <p className="mb-6 text-[14px] text-ink-3">
        Renseignez votre email. Vous recevrez un lien pour obtenir un mot de passe temporaire que
        vous pourrez modifier depuis votre profil.
      </p>

      {message !== null ? (
        <p className="rounded-lg border border-success/20 bg-success/10 px-3 py-2 text-sm text-success">
          {message}
        </p>
      ) : (
        <>
          {error !== null && (
            <p className="mb-4 rounded-lg border border-danger/20 bg-danger/10 px-3 py-2 text-sm text-danger">
              {error}
            </p>
          )}
          <form
            onSubmit={(e) => {
              e.preventDefault();
              void handleRequestReset();
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
        </>
      )}
    </div>
  );
}
