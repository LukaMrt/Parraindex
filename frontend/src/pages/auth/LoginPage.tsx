import { useState } from 'react';
import { useNavigate, Link } from 'react-router';
import { useAuth } from '../../hooks/useAuth';
import { Button, Input } from '../../components/ui';

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

    const result = await login({ email, password });
    if (result.ok) {
      void navigate('/');
    } else {
      setError(result.message ?? 'Connexion impossible');
    }

    setSubmitting(false);
  }

  return (
    <div className="mx-auto max-w-sm px-4 py-16">
      <h1 className="mb-6 text-[22px] font-semibold tracking-tight text-ink">Connexion</h1>

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
        <Input
          type="email"
          placeholder="Email universitaire"
          value={email}
          onChange={(e) => {
            setEmail(e.target.value);
          }}
          required
        />
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
          {submitting ? 'Connexion…' : 'Se connecter'}
        </Button>
      </form>

      <div className="mt-5 flex flex-col gap-1.5 text-sm text-ink-3">
        <Link to="/reset-password" className="transition-colors hover:text-ink">
          Mot de passe oublié ?
        </Link>
        <Link to="/register" className="transition-colors hover:text-ink">
          Créer un compte
        </Link>
      </div>
    </div>
  );
}
