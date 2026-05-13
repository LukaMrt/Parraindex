import { useEffect, useRef, useState } from 'react';
import { useNavigate } from 'react-router';
import { verifyEmail } from '../../lib/api/auth';
import { useNotification } from '../../hooks/useNotification';

export function VerifyEmailPage() {
  const navigate = useNavigate();
  const { notify } = useNotification();
  const [status, setStatus] = useState<'pending' | 'success' | 'error'>('pending');
  const called = useRef(false);

  useEffect(() => {
    if (called.current) return;
    called.current = true;

    const queryString = window.location.search.slice(1);

    void verifyEmail(queryString).then((result) => {
      if (result.ok) {
        setStatus('success');
        notify('success', 'Votre email a été confirmé. Vous pouvez maintenant vous connecter.');
        void navigate('/login');
      } else {
        setStatus('error');
        notify('error', result.error.message || 'Le lien de confirmation est invalide ou expiré.');
      }
    });
  }, [navigate, notify]);

  return (
    <div className="mx-auto max-w-sm px-4 py-24 text-center">
      {status === 'pending' && <p className="text-ink-3 text-sm">Vérification en cours…</p>}
      {status === 'error' && (
        <p className="text-danger text-sm">
          Le lien est invalide ou a expiré. Veuillez vous réinscrire ou contacter un administrateur.
        </p>
      )}
    </div>
  );
}
