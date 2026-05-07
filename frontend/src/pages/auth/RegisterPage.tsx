import { useState } from 'react';
import { useNavigate, Link } from 'react-router';
import { register } from '../../lib/api/auth';
import { Button, Input } from '../../components/ui';

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
            placeholder="prenom.nom@etu.univ-lyon1.fr"
            value={email}
            onChange={(e) => {
              setEmail(e.target.value);
            }}
            required
          />
          <p className="mt-1 text-xs text-ink-3">Email universitaire obligatoire</p>
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
