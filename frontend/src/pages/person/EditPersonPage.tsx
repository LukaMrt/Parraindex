import { useEffect, useRef, useState } from 'react';
import type { ChangeEvent, SyntheticEvent } from 'react';
import { useNavigate, useParams } from 'react-router';
import { PersonCard } from '../../components/PersonCard';
import { useAuth } from '../../hooks/useAuth';
import { deletePerson, getPerson, updatePerson, uploadPicture } from '../../lib/api/persons';
import type { Person, PersonRequest } from '../../types/person';

export function EditPersonPage() {
  const { id } = useParams<{ id: string }>();
  const { user } = useAuth();
  const navigate = useNavigate();

  const [person, setPerson] = useState<Person | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [biography, setBiography] = useState('');
  const [description, setDescription] = useState('');
  const [pendingPicture, setPendingPicture] = useState<File | null>(null);
  const [picturePreview, setPicturePreview] = useState<string | null>(null);

  const fileInputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (!id) return;
    void getPerson(Number(id)).then((result) => {
      if (!result.ok) {
        setError('Personne introuvable');
        setLoading(false);
        return;
      }
      const p = result.data;
      setPerson(p);
      setFirstName(p.firstName);
      setLastName(p.lastName);
      setBiography(p.biography ?? '');
      setDescription(p.description ?? '');
      setLoading(false);
    });
  }, [id]);

  function handlePictureChange(e: ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0];
    if (!file) return;
    setPendingPicture(file);
    setPicturePreview(URL.createObjectURL(file));
  }

  async function handleSubmit(e: SyntheticEvent) {
    e.preventDefault();
    if (!person || !id) return;
    setSaving(true);
    setError('');

    const data: PersonRequest = {
      firstName: user?.isAdmin ? firstName : person.firstName,
      lastName: user?.isAdmin ? lastName : person.lastName,
      startYear: person.startYear,
      biography: biography || null,
      description: description || null,
    };

    const updateResult = await updatePerson(Number(id), data);
    if (!updateResult.ok) {
      setError(updateResult.error.message);
      setSaving(false);
      return;
    }

    if (pendingPicture) {
      const picResult = await uploadPicture(Number(id), pendingPicture);
      if (!picResult.ok) {
        setError(picResult.error.message);
        setSaving(false);
        return;
      }
    }

    void navigate(`/personne/${id}`);
  }

  async function handleDelete() {
    if (!id || !window.confirm('Supprimer définitivement ce profil ?')) return;
    const result = await deletePerson(Number(id));
    if (result.ok) void navigate('/tree');
    else setError(result.error.message);
  }

  if (loading) return <div className="p-8 text-medium-blue">Chargement…</div>;
  if (!person) return <div className="p-8 text-medium-red">{error || 'Personne introuvable.'}</div>;

  const previewPerson = {
    ...person,
    firstName,
    lastName,
    picture: picturePreview ?? person.picture,
    fullName: `${firstName} ${lastName}`,
  };

  return (
    <form
      onSubmit={(e) => {
        void handleSubmit(e);
      }}
      className="mx-auto max-w-5xl px-6 py-8"
    >
      {error && (
        <div className="mb-4 rounded bg-light-red px-4 py-2 text-sm text-dark-red">{error}</div>
      )}

      <div className="grid gap-8 md:grid-cols-3">
        {/* Biographie + À propos */}
        <div className="md:col-span-2 space-y-6">
          <div>
            <h2 className="mb-2 text-sm font-semibold uppercase text-medium-blue">Biographie</h2>
            <textarea
              value={biography}
              onChange={(e) => {
                setBiography(e.target.value);
              }}
              placeholder="Courte présentation sur votre carte"
              rows={4}
              className="w-full rounded border border-medium-grey p-3 text-sm text-dark-blue outline-none focus:border-light-blue"
            />
          </div>
          <div>
            <h2 className="mb-2 text-sm font-semibold uppercase text-medium-blue">À propos</h2>
            <textarea
              value={description}
              onChange={(e) => {
                setDescription(e.target.value);
              }}
              placeholder="Présentation complète sur votre page personnelle"
              rows={6}
              className="w-full rounded border border-medium-grey p-3 text-sm text-dark-blue outline-none focus:border-light-blue"
            />
          </div>

          {user?.isAdmin && (
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="mb-1 block text-sm font-semibold uppercase text-medium-blue">
                  Prénom
                </label>
                <input
                  type="text"
                  value={firstName}
                  onChange={(e) => {
                    setFirstName(e.target.value);
                  }}
                  className="w-full rounded border border-medium-grey p-2 text-sm outline-none focus:border-light-blue"
                />
              </div>
              <div>
                <label className="mb-1 block text-sm font-semibold uppercase text-medium-blue">
                  Nom
                </label>
                <input
                  type="text"
                  value={lastName}
                  onChange={(e) => {
                    setLastName(e.target.value);
                  }}
                  className="w-full rounded border border-medium-grey p-2 text-sm outline-none focus:border-light-blue"
                />
              </div>
            </div>
          )}
        </div>

        {/* Preview + Photo */}
        <div className="flex flex-col items-center gap-4">
          <PersonCard person={previewPerson} isCentered />

          <button
            type="button"
            onClick={() => {
              fileInputRef.current?.click();
            }}
            className="text-sm text-light-blue underline"
          >
            Changer la photo
          </button>
          <input
            ref={fileInputRef}
            type="file"
            accept="image/*"
            className="hidden"
            onChange={handlePictureChange}
          />

          {picturePreview && (
            <img
              src={picturePreview}
              alt="Aperçu"
              className="h-20 w-20 rounded-full object-cover shadow"
            />
          )}
        </div>
      </div>

      {/* Actions */}
      <div className="mt-8 flex items-center justify-between">
        <button
          type="button"
          onClick={() => {
            void navigate(`/personne/${id}`);
          }}
          className="text-medium-blue hover:text-dark-blue"
        >
          ← Retour
        </button>
        <div className="flex gap-3">
          {user?.isAdmin && (
            <button
              type="button"
              onClick={() => {
                void handleDelete();
              }}
              className="rounded border border-dark-red px-4 py-2 text-sm text-dark-red hover:bg-light-red"
            >
              Supprimer
            </button>
          )}
          <button
            type="submit"
            disabled={saving}
            className="rounded bg-dark-blue px-5 py-2 text-sm text-white hover:bg-medium-blue disabled:opacity-60"
          >
            {saving ? 'Enregistrement…' : 'Enregistrer'}
          </button>
          <a
            href={`/api/persons/${person.id}/export`}
            className="rounded border border-medium-grey px-4 py-2 text-sm text-medium-blue hover:bg-light-grey"
            title="Télécharger vos données"
          >
            ⬇
          </a>
        </div>
      </div>
    </form>
  );
}
