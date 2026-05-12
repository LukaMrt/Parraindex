import { useState } from 'react';
import { useNavigate, Link } from 'react-router';
import { register } from '../../lib/api/auth';
import { Button, Input } from '../../components/ui';
import { useNotification } from '../../hooks/useNotification';

const UNIVERSITY_EMAIL_REGEX = /^[a-zA-Z-]+\.[a-zA-Z-]+@etu\.univ-lyon1\.fr$/;

export function RegisterPage() {
  const navigate = useNavigate();
  const { notify } = useNotification();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit() {
    setSubmitting(true);
    setError(null);

    const isUniversityEmail = UNIVERSITY_EMAIL_REGEX.test(email);

    if (!isUniversityEmail) {
      void navigate('/select-person', { state: { email, password } });
      return;
    }

    const result = await register({ email, password });

    if (result.ok) {
      notify('success', 'Compte créé ! Un email de confirmation vous a été envoyé.');
      void navigate('/login');
    } else {
      const msg = result.error.message;
      const needsManualSelection = msg.includes('Aucune personne') || msg.includes('manuellement');

      if (needsManualSelection) {
        void navigate('/select-person', { state: { email, password } });
      } else {
        setError(msg);
        setSubmitting(false);
      }
    }
  }

  return (
    <div className="mx-auto max-w-sm px-4 py-16">
      <h1 className="mb-6 text-[22px] font-semibold tracking-tight text-ink">Créer un compte</h1>

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
        <div>
          <Input
            type="email"
            placeholder="votre@email.com"
            value={email}
            onChange={(e) => {
              setEmail(e.target.value);
            }}
            required
          />
          <p className="mt-1 text-xs text-ink-3">
            Email universitaire Lyon 1 ou adresse personnelle
          </p>
        </div>
        <Input
          type="password"
          placeholder="Mot de passe"
          value={password}
          onChange={(e) => {
            setPassword(e.target.value);
          }}
          required
        />
        <Button type="submit" size="lg" disabled={submitting} className="mt-1 w-full">
          {submitting ? 'Inscription…' : "S'inscrire"}
        </Button>
      </form>

      <div className="mt-5 text-sm text-ink-3">
        Déjà un compte ?{' '}
        <Link to="/login" className="transition-colors hover:text-ink">
          Se connecter
        </Link>
      </div>
    </div>
  );
}
