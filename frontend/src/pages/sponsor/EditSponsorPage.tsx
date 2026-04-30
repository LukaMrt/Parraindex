import { useEffect, useState } from 'react';
import type { SyntheticEvent } from 'react';
import { useNavigate, useParams } from 'react-router';
import { getSponsor, updateSponsor, deleteSponsor } from '../../lib/api/sponsors';
import type { Sponsor, SponsorType } from '../../types/sponsor';

const TYPE_OPTIONS: { value: SponsorType; label: string }[] = [
  { value: 'HEART', label: 'Parrainage de cœur' },
  { value: 'CLASSIC', label: 'Parrainage classique' },
  { value: 'UNKNOWN', label: 'Lien inconnu' },
];

export function EditSponsorPage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();

  const [sponsor, setSponsor] = useState<Sponsor | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  const [type, setType] = useState<SponsorType>('CLASSIC');
  const [date, setDate] = useState('');
  const [description, setDescription] = useState('');

  useEffect(() => {
    if (!id) return;
    void getSponsor(Number(id)).then((result) => {
      if (!result.ok) {
        setError('Parrainage introuvable');
        setLoading(false);
        return;
      }
      const s = result.data;
      setSponsor(s);
      setType(s.type);
      setDate(s.date ?? '');
      setDescription(s.description ?? '');
      setLoading(false);
    });
  }, [id]);

  async function handleSubmit(e: SyntheticEvent) {
    e.preventDefault();
    if (!sponsor || !id) return;
    setSaving(true);
    setError('');

    const result = await updateSponsor(Number(id), {
      godFatherId: sponsor.godFatherId,
      godChildId: sponsor.godChildId,
      type,
      date: date || null,
      description: description || null,
    });

    if (result.ok) void navigate(`/parrainage/${id}`);
    else {
      setError(result.error.message);
      setSaving(false);
    }
  }

  async function handleDelete() {
    if (!id || !window.confirm('Supprimer ce lien de parrainage ?')) return;
    const result = await deleteSponsor(Number(id));
    if (result.ok) void navigate('/tree');
    else setError(result.error.message);
  }

  if (loading) return <div className="p-8 text-medium-blue">Chargement…</div>;
  if (!sponsor) return <div className="p-8 text-medium-red">{error || 'Introuvable.'}</div>;

  return (
    <div className="mx-auto max-w-xl px-6 py-10">
      <h1 className="mb-6 text-2xl font-bold text-dark-blue">Gestion de lien</h1>

      <div className="mb-6 text-sm text-medium-blue">
        {sponsor.godFatherName} → {sponsor.godChildName}
      </div>

      {error && (
        <div className="mb-4 rounded bg-light-red px-4 py-2 text-sm text-dark-red">{error}</div>
      )}

      <form
        onSubmit={(e) => {
          void handleSubmit(e);
        }}
        className="space-y-5"
      >
        <div>
          <label className="mb-1 block text-sm font-semibold text-medium-blue">
            Type de parrainage
          </label>
          <select
            value={type}
            onChange={(e) => {
              setType(e.target.value as SponsorType);
            }}
            className="w-full rounded border border-medium-grey p-2 text-sm text-dark-blue outline-none focus:border-light-blue"
          >
            {TYPE_OPTIONS.map((o) => (
              <option key={o.value} value={o.value}>
                {o.label}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label className="mb-1 block text-sm font-semibold text-medium-blue">
            Date du parrainage
          </label>
          <input
            type="date"
            value={date}
            onChange={(e) => {
              setDate(e.target.value);
            }}
            className="w-full rounded border border-medium-grey p-2 text-sm text-dark-blue outline-none focus:border-light-blue"
          />
        </div>

        <div>
          <label className="mb-1 block text-sm font-semibold text-medium-blue">Description</label>
          <textarea
            value={description}
            onChange={(e) => {
              setDescription(e.target.value);
            }}
            rows={4}
            className="w-full rounded border border-medium-grey p-2 text-sm text-dark-blue outline-none focus:border-light-blue"
          />
        </div>

        <div className="flex gap-3 pt-2">
          <button
            type="button"
            onClick={() => {
              void handleDelete();
            }}
            className="rounded border border-dark-red px-4 py-2 text-sm text-dark-red hover:bg-light-red"
          >
            Supprimer
          </button>
          <button
            type="submit"
            disabled={saving}
            className="ml-auto rounded bg-dark-blue px-5 py-2 text-sm text-white hover:bg-medium-blue disabled:opacity-60"
          >
            {saving ? 'Enregistrement…' : 'Valider'}
          </button>
        </div>
      </form>
    </div>
  );
}
