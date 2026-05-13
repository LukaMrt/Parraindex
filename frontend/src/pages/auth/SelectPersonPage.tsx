import { useState, useEffect, useRef } from 'react';
import { useNavigate, useLocation, Link } from 'react-router';
import { register, getAvailablePersons } from '../../lib/api/auth';
import type { AvailablePerson } from '../../types/auth';
import { Button, Input } from '../../components/ui';
import { contrastColor } from '../../lib/colors';
import { useNotification } from '../../hooks/useNotification';

const PAGE_SIZE = 20;

interface LocationState {
  email: string;
  password: string;
}

export function SelectPersonPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const { notify } = useNotification();
  const state = location.state as LocationState | null;

  const [persons, setPersons] = useState<AvailablePerson[]>([]);
  const [total, setTotal] = useState<number | null>(null);
  const [search, setSearch] = useState('');
  const [selectedId, setSelectedId] = useState<number | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  const loadedAll = total !== null && persons.length >= total;
  const isLoading = total === null;

  const abortRef = useRef<AbortController | null>(null);

  useEffect(() => {
    if (!state?.email || !state.password) {
      void navigate('/register', { replace: true });
      return;
    }

    abortRef.current = new AbortController();
    const { signal } = abortRef.current;

    async function loadAll() {
      let page = 1;

      for (;;) {
        const result = await getAvailablePersons(page, PAGE_SIZE);
        if (signal.aborted) return;
        if (!result.ok) break;

        setTotal(result.data.total);
        setPersons((prev) => [...prev, ...result.data.items]);

        if (page * PAGE_SIZE >= result.data.total) break;
        page++;
      }
    }

    loadAll().catch(console.error);
    return () => {
      abortRef.current?.abort();
    };
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  const filtered = persons.filter((p) => p.fullName.toLowerCase().includes(search.toLowerCase()));

  async function handleSubmit() {
    if (selectedId === null || !state) return;

    setSubmitting(true);
    setError(null);

    const result = await register({
      email: state.email,
      password: state.password,
      personId: selectedId,
    });

    if (result.ok) {
      notify('warning', 'Compte créé ! Il sera activé après validation par un administrateur.');
      void navigate('/login');
    } else {
      setError(result.error.message);
      setSubmitting(false);
    }
  }

  if (!state) return null;

  return (
    <div className="mx-auto max-w-lg px-4 py-16">
      <h1 className="mb-2 text-[22px] font-semibold tracking-tight text-ink">Qui êtes-vous ?</h1>
      <p className="mb-6 text-sm text-ink-3">
        Sélectionnez votre profil dans la liste. Votre compte sera activé après validation par un
        administrateur.
      </p>

      {error !== null && (
        <p className="mb-4 rounded-lg border border-danger/20 bg-danger/10 px-3 py-2 text-sm text-danger">
          {error}
        </p>
      )}

      <div className="relative mb-4">
        <Input
          placeholder="Rechercher par nom…"
          value={search}
          onChange={(e) => {
            setSearch(e.target.value);
          }}
        />
        {!loadedAll && (
          <span className="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-ink-3">
            {persons.length}
            {total !== null ? `/${total}` : ''} chargés…
          </span>
        )}
      </div>

      <div className="mb-6 max-h-80 overflow-y-auto rounded-xl border border-line">
        {isLoading ? (
          <p className="px-4 py-6 text-center text-sm text-ink-3">Chargement…</p>
        ) : filtered.length === 0 ? (
          <p className="px-4 py-6 text-center text-sm text-ink-3">Aucune personne trouvée</p>
        ) : (
          filtered.map((person) => (
            <button
              key={person.id}
              type="button"
              onClick={() => {
                setSelectedId(person.id);
              }}
              className={[
                'flex w-full items-center gap-3 border-b border-line px-4 py-3 text-left transition-colors last:border-b-0',
                selectedId === person.id ? 'bg-surface' : 'hover:bg-surface/50',
              ].join(' ')}
            >
              <div
                className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold"
                style={{
                  backgroundColor: person.color,
                  color: contrastColor(person.color),
                }}
              >
                {person.firstName[0]}
                {person.lastName[0]}
              </div>
              <div className="min-w-0">
                <p className="truncate text-sm font-medium text-ink">{person.fullName}</p>
                <p className="text-xs text-ink-3">Promo {person.startYear}</p>
              </div>
              {selectedId === person.id && (
                <span className="ml-auto text-xs font-medium text-ink">✓</span>
              )}
            </button>
          ))
        )}
      </div>

      <Button
        size="lg"
        className="w-full"
        disabled={selectedId === null || submitting}
        onClick={() => {
          void handleSubmit();
        }}
      >
        {submitting ? 'Inscription…' : 'Confirmer'}
      </Button>

      <div className="mt-4 text-sm text-ink-3">
        <Link to="/register" className="transition-colors hover:text-ink">
          ← Retour
        </Link>
      </div>
    </div>
  );
}
