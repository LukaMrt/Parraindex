import { useEffect, useRef, useState } from 'react';
import type { ChangeEvent, KeyboardEvent, SyntheticEvent } from 'react';
import type { ReactNode } from 'react';
import { Navigate, useNavigate, useParams } from 'react-router';
import { Avatar, Breadcrumb, Button, Card, Input, Skeleton, StatCard } from '../../components/ui';
import { useAuth } from '../../hooks/useAuth';
import {
  deletePerson,
  getPerson,
  getPersons,
  updatePerson,
  uploadPicture,
} from '../../lib/api/persons';
import { createSponsor, deleteSponsor } from '../../lib/api/sponsors';
import { promoColor } from '../../lib/colors';
import { SPONSOR_TYPE_ICONS, SPONSOR_TYPE_LABELS } from '../../lib/sponsorTypes';
import type { Person, PersonRequest, PersonSummary } from '../../types/person';
import type { SponsorSummary, SponsorType } from '../../types/sponsor';

// ── Skeleton ──────────────────────────────────────────────────────────────────

function EditPersonPageSkeleton() {
  return (
    <div className="mx-auto max-w-[980px] px-7 py-7">
      <Skeleton className="mb-6 h-4 w-48" />
      <Skeleton className="mb-5 h-52 w-full rounded-2xl" />
      <div className="mb-5 grid grid-cols-3 gap-3">
        {[0, 1, 2].map((i) => (
          <Skeleton key={i} className="h-20 rounded-xl" />
        ))}
      </div>
      <Skeleton className="h-48 w-full rounded-xl" />
    </div>
  );
}

// ── Label helper ──────────────────────────────────────────────────────────────

function FieldLabel({ children }: { children: ReactNode }) {
  return (
    <p className="mb-1 text-[10.5px] font-semibold uppercase tracking-widest text-ink-3">
      {children}
    </p>
  );
}

// ── Autocomplete personne ─────────────────────────────────────────────────────

function PersonAutocomplete({
  persons,
  excludeId,
  value,
  onSelect,
  placeholder,
}: {
  persons: PersonSummary[];
  excludeId: number;
  value: PersonSummary | null;
  onSelect: (p: PersonSummary | null) => void;
  placeholder?: string;
}) {
  const [query, setQuery] = useState(value?.fullName ?? '');
  const [open, setOpen] = useState(false);
  const [cursor, setCursor] = useState(0);
  const ref = useRef<HTMLDivElement>(null);

  const filtered = query.trim()
    ? persons
        .filter((p) => p.id !== excludeId && p.fullName.toLowerCase().includes(query.toLowerCase()))
        .slice(0, 8)
    : [];

  const displayQuery = value ? value.fullName : query;

  useEffect(() => {
    function onClickOutside(e: MouseEvent) {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    }
    document.addEventListener('mousedown', onClickOutside);
    return () => {
      document.removeEventListener('mousedown', onClickOutside);
    };
  }, []);

  function pick(p: PersonSummary) {
    onSelect(p);
    setQuery(p.fullName);
    setOpen(false);
  }

  function handleKeyDown(e: KeyboardEvent) {
    if (!open || filtered.length === 0) return;
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      setCursor((c) => Math.min(c + 1, filtered.length - 1));
    }
    if (e.key === 'ArrowUp') {
      e.preventDefault();
      setCursor((c) => Math.max(c - 1, 0));
    }
    if (e.key === 'Enter') {
      e.preventDefault();
      const p = filtered[cursor];
      if (p) pick(p);
    }
    if (e.key === 'Escape') {
      setOpen(false);
    }
  }

  return (
    <div ref={ref} className="relative">
      <Input
        value={displayQuery}
        onChange={(e) => {
          setQuery(e.target.value);
          onSelect(null);
          setOpen(true);
          setCursor(0);
        }}
        onFocus={() => {
          if (displayQuery) setOpen(true);
        }}
        onKeyDown={handleKeyDown}
        placeholder={placeholder ?? 'Rechercher une personne…'}
      />
      {open && filtered.length > 0 && (
        <ul className="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-xl border border-line bg-surface shadow-lg">
          {filtered.map((p, i) => (
            <li
              key={p.id}
              onMouseDown={() => {
                pick(p);
              }}
              className={`flex cursor-pointer select-none items-center gap-2.5 px-3 py-2 text-[13px] transition-colors ${
                i === cursor ? 'bg-bg text-ink' : 'text-ink-2 hover:bg-bg'
              }`}
            >
              <Avatar person={p} size={24} square />
              <span className="font-medium">{p.fullName}</span>
              <span className="ml-auto text-[11px] text-ink-4">Promo {p.startYear}</span>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}

// ── Formulaire ajout parrainage ───────────────────────────────────────────────

function AddSponsorForm({
  personId,
  persons,
  onAdded,
}: {
  personId: number;
  persons: PersonSummary[];
  onAdded: (s: SponsorSummary) => void;
}) {
  const [role, setRole] = useState<'godChild' | 'godFather'>('godChild');
  const [otherPerson, setOtherPerson] = useState<PersonSummary | null>(null);
  const [type, setType] = useState<SponsorType>('CLASSIC');
  const [date, setDate] = useState('');
  const [description, setDescription] = useState('');
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  async function handleAdd() {
    if (!otherPerson) {
      setError('Sélectionnez une personne');
      return;
    }
    setSaving(true);
    setError('');

    const result = await createSponsor({
      godFatherId: role === 'godFather' ? personId : otherPerson.id,
      godChildId: role === 'godChild' ? personId : otherPerson.id,
      type,
      date: date || null,
      description: description || null,
    });

    if (!result.ok) {
      setError(result.error.message);
      setSaving(false);
      return;
    }

    onAdded({
      id: result.data.id,
      godFatherId: result.data.godFatherId,
      godFatherName: result.data.godFatherName,
      godChildId: result.data.godChildId,
      godChildName: result.data.godChildName,
      type: result.data.type,
      date: result.data.date,
    });

    setOtherPerson(null);
    setDate('');
    setDescription('');
    setSaving(false);
  }

  const TYPE_OPTIONS: { value: SponsorType; label: string }[] = [
    { value: 'CLASSIC', label: 'IUT' },
    { value: 'HEART', label: 'De cœur' },
    { value: 'UNKNOWN', label: 'Inconnu' },
  ];

  return (
    <div className="rounded-xl border border-dashed border-line bg-bg p-4">
      <p className="mb-3 text-[12px] font-semibold uppercase tracking-widest text-ink-3">
        Ajouter un parrainage
      </p>

      {error && <p className="mb-3 text-[12px] text-red-600">{error}</p>}

      <div className="grid gap-3 sm:grid-cols-2">
        {/* Rôle */}
        <div>
          <FieldLabel>Mon rôle</FieldLabel>
          <div className="flex gap-2">
            {(['godFather', 'godChild'] as const).map((r) => (
              <button
                key={r}
                type="button"
                onClick={() => {
                  setRole(r);
                }}
                className={`flex-1 rounded-[9px] border px-3 py-1.5 text-[12.5px] font-medium transition-colors ${
                  role === r
                    ? 'border-ink bg-ink text-white'
                    : 'border-line bg-surface text-ink-2 hover:border-ink-3'
                } cursor-pointer`}
              >
                {r === 'godFather' ? 'Parrain' : 'Fillot'}
              </button>
            ))}
          </div>
        </div>

        {/* Personne */}
        <div>
          <FieldLabel>{role === 'godFather' ? 'Fillot' : 'Parrain'}</FieldLabel>
          <PersonAutocomplete
            persons={persons}
            excludeId={personId}
            value={otherPerson}
            onSelect={setOtherPerson}
          />
        </div>

        {/* Type */}
        <div>
          <FieldLabel>Type</FieldLabel>
          <div className="flex gap-2">
            {TYPE_OPTIONS.map((o) => (
              <button
                key={o.value}
                type="button"
                onClick={() => {
                  setType(o.value);
                }}
                className={`flex-1 rounded-[9px] border px-2 py-1.5 text-[12px] font-medium transition-colors ${
                  type === o.value
                    ? 'border-ink bg-ink text-white'
                    : 'border-line bg-surface text-ink-2 hover:border-ink-3'
                } cursor-pointer`}
              >
                {SPONSOR_TYPE_ICONS[o.value]} {o.label}
              </button>
            ))}
          </div>
        </div>

        {/* Date */}
        <div>
          <FieldLabel>Date</FieldLabel>
          <Input
            type="date"
            value={date}
            onChange={(e) => {
              setDate(e.target.value);
            }}
          />
        </div>

        {/* Description */}
        <div className="sm:col-span-2">
          <FieldLabel>Description (optionnel)</FieldLabel>
          <textarea
            value={description}
            onChange={(e) => {
              setDescription(e.target.value);
            }}
            rows={2}
            placeholder="Anecdote, contexte…"
            className="w-full resize-none rounded-[9px] border border-line bg-surface px-3.5 py-2.5 text-sm text-ink outline-none transition-colors placeholder:text-ink-4 focus:border-ink-2"
          />
        </div>
      </div>

      <div className="mt-3 flex justify-end">
        <Button
          type="button"
          size="sm"
          disabled={saving || !otherPerson}
          onClick={() => void handleAdd()}
        >
          {saving ? 'Ajout…' : 'Ajouter'}
        </Button>
      </div>
    </div>
  );
}

// ── Ligne parrainage existant ─────────────────────────────────────────────────

function SponsorRow({
  sponsor,
  personId,
  onDelete,
}: {
  sponsor: SponsorSummary;
  personId: number;
  onDelete: (id: number) => void;
}) {
  const isGodFather = sponsor.godFatherId === personId;
  const relatedName = isGodFather ? sponsor.godChildName : sponsor.godFatherName;
  const role = isGodFather ? 'Fillot' : 'Parrain';
  const startYear = new Date(sponsor.date ?? '').getFullYear() || 2020;
  const color = promoColor(startYear);
  const [confirm, setConfirm] = useState(false);
  const [deleting, setDeleting] = useState(false);
  const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(
    () => () => {
      if (timerRef.current) clearTimeout(timerRef.current);
    },
    [],
  );

  function handleDeleteClick() {
    if (!confirm) {
      setConfirm(true);
      timerRef.current = setTimeout(() => {
        setConfirm(false);
      }, 5000);
      return;
    }
    if (timerRef.current) clearTimeout(timerRef.current);
    setDeleting(true);
    void deleteSponsor(sponsor.id).then((result) => {
      if (result.ok) onDelete(sponsor.id);
      else {
        setDeleting(false);
        setConfirm(false);
      }
    });
  }

  return (
    <div className="flex items-center gap-3 rounded-xl border border-line bg-surface p-3">
      <Avatar
        person={{
          firstName: relatedName.split(' ')[0] ?? '',
          lastName: relatedName.split(' ').slice(1).join(' '),
          fullName: relatedName,
          picture: null,
          startYear,
        }}
        size={36}
        square
      />
      <div className="min-w-0 flex-1">
        <p className="truncate text-[13px] font-medium text-ink">{relatedName}</p>
        <p className="text-[11.5px]" style={{ color }}>
          {role} · {SPONSOR_TYPE_ICONS[sponsor.type]} Parrainage {SPONSOR_TYPE_LABELS[sponsor.type]}
          {sponsor.date && ` · ${new Date(sponsor.date).getFullYear()}`}
        </p>
      </div>
      <button
        type="button"
        disabled={deleting}
        onClick={handleDeleteClick}
        className={`shrink-0 cursor-pointer rounded-lg transition-all disabled:cursor-not-allowed disabled:opacity-40 ${
          confirm
            ? 'bg-red-500 px-2.5 py-1 text-[11.5px] font-semibold text-white hover:bg-red-600'
            : 'p-1.5 text-ink-4 hover:bg-bg hover:text-red-500'
        }`}
      >
        {confirm ? (
          deleting ? (
            '…'
          ) : (
            'Confirmer'
          )
        ) : (
          <svg
            width="15"
            height="15"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
          >
            <polyline points="3 6 5 6 21 6" />
            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
            <path d="M10 11v6M14 11v6" />
            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
          </svg>
        )}
      </button>
    </div>
  );
}

// ── Hero éditable ─────────────────────────────────────────────────────────────

function EditPersonHero({
  person,
  firstName,
  lastName,
  startYear,
  biography,
  description,
  picturePreview,
  canEditIdentity,
  onFirstNameChange,
  onLastNameChange,
  onStartYearChange,
  onBiographyChange,
  onDescriptionChange,
  onFileChange,
}: {
  person: Person;
  firstName: string;
  lastName: string;
  startYear: number;
  biography: string;
  description: string;
  picturePreview: string | null;
  canEditIdentity: boolean;
  onFirstNameChange: (v: string) => void;
  onLastNameChange: (v: string) => void;
  onStartYearChange: (v: number) => void;
  onBiographyChange: (v: string) => void;
  onDescriptionChange: (v: string) => void;
  onFileChange: (file: File) => void;
}) {
  const fileRef = useRef<HTMLInputElement>(null);
  const [avatarHover, setAvatarHover] = useState(false);
  const color = promoColor(startYear);

  const displayPerson = {
    ...person,
    picture: picturePreview ?? person.picture,
    firstName,
    lastName,
    startYear,
    fullName: `${firstName} ${lastName}`,
  };

  return (
    <div className="relative mb-5 overflow-hidden rounded-2xl border border-line bg-surface p-8">
      <div
        className="pointer-events-none absolute inset-0"
        style={{ background: `linear-gradient(135deg, ${color}12 0%, transparent 55%)` }}
      />
      <div className="relative flex flex-wrap items-start gap-7">
        {/* Avatar cliquable */}
        <div className="shrink-0">
          <FieldLabel>Photo</FieldLabel>
          <button
            type="button"
            className="relative cursor-pointer overflow-hidden rounded-[18px] focus:outline-none"
            style={{
              width: 140,
              height: 140,
              boxShadow: `0 0 0 3px ${color}, 0 12px 32px ${color}30`,
            }}
            onClick={() => fileRef.current?.click()}
            onMouseEnter={() => {
              setAvatarHover(true);
            }}
            onMouseLeave={() => {
              setAvatarHover(false);
            }}
            title="Changer la photo"
          >
            <Avatar person={displayPerson} fill />
            {avatarHover && (
              <div className="absolute inset-0 flex flex-col items-center justify-center gap-1 bg-black/50">
                <svg
                  width="20"
                  height="20"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="white"
                  strokeWidth="2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                >
                  <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                  <circle cx="12" cy="13" r="4" />
                </svg>
                <span className="text-[11px] font-medium text-white">Modifier</span>
              </div>
            )}
          </button>
          <input
            ref={fileRef}
            type="file"
            accept="image/*"
            className="hidden"
            onChange={(e: ChangeEvent<HTMLInputElement>) => {
              const file = e.target.files?.[0];
              if (file) onFileChange(file);
            }}
          />
        </div>

        {/* Champs */}
        <div className="min-w-[240px] flex-1">
          <div className="mb-4 flex flex-wrap gap-3">
            {canEditIdentity && (
              <>
                <div>
                  <FieldLabel>Prénom</FieldLabel>
                  <Input
                    value={firstName}
                    onChange={(e) => {
                      onFirstNameChange(e.target.value);
                    }}
                    placeholder="Prénom"
                    className="w-40"
                  />
                </div>
                <div>
                  <FieldLabel>Nom</FieldLabel>
                  <Input
                    value={lastName}
                    onChange={(e) => {
                      onLastNameChange(e.target.value);
                    }}
                    placeholder="Nom"
                    className="w-40"
                  />
                </div>
              </>
            )}
            {!canEditIdentity && (
              <div>
                <FieldLabel>Nom</FieldLabel>
                <p className="text-[22px] font-semibold leading-tight tracking-tight text-ink">
                  {firstName} <span className="text-ink-2">{lastName}</span>
                </p>
              </div>
            )}
            <div>
              <FieldLabel>Promotion</FieldLabel>
              <Input
                type="number"
                value={startYear}
                onChange={(e) => {
                  onStartYearChange(Number(e.target.value));
                }}
                className="w-24"
                min={2000}
                max={2099}
              />
            </div>
          </div>

          <div className="space-y-2 max-w-[540px]">
            <div>
              <FieldLabel>Description</FieldLabel>
              <textarea
                value={description}
                onChange={(e) => {
                  onDescriptionChange(e.target.value);
                }}
                placeholder="Courte description visible sur votre page…"
                rows={2}
                className="w-full resize-none rounded-[9px] border border-line bg-bg px-3.5 py-2.5 text-[14px] leading-relaxed text-ink-2 outline-none transition-colors placeholder:text-ink-4 focus:border-ink-2"
              />
            </div>
            <div>
              <FieldLabel>Biographie</FieldLabel>
              <textarea
                value={biography}
                onChange={(e) => {
                  onBiographyChange(e.target.value);
                }}
                placeholder="Biographie affichée en italique…"
                rows={2}
                className="w-full resize-none rounded-[9px] border border-line bg-bg px-3.5 py-2.5 text-[13.5px] leading-relaxed text-ink-2 outline-none transition-colors placeholder:text-ink-4 focus:border-ink-2"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

// ── Page ──────────────────────────────────────────────────────────────────────

export function EditPersonPage() {
  const { id } = useParams<{ id: string }>();
  const { user } = useAuth();
  const navigate = useNavigate();

  const [person, setPerson] = useState<Person | null>(null);
  const [allPersons, setAllPersons] = useState<PersonSummary[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [startYear, setStartYear] = useState(new Date().getFullYear());
  const [biography, setBiography] = useState('');
  const [description, setDescription] = useState('');
  const [pendingPicture, setPendingPicture] = useState<File | null>(null);
  const [picturePreview, setPicturePreview] = useState<string | null>(null);
  const [sponsors, setSponsors] = useState<SponsorSummary[]>([]);

  useEffect(() => {
    if (!id) return;
    void Promise.all([getPerson(Number(id)), getPersons()]).then(
      ([personResult, personsResult]) => {
        if (!personResult.ok) {
          setError('Personne introuvable');
          setLoading(false);
          return;
        }
        const p = personResult.data;
        setPerson(p);
        setFirstName(p.firstName);
        setLastName(p.lastName);
        setStartYear(p.startYear);
        setBiography(p.biography ?? '');
        setDescription(p.description ?? '');
        setSponsors([...p.godFathers, ...p.godChildren]);
        if (personsResult.ok) setAllPersons(personsResult.data);
        setLoading(false);
      },
    );
  }, [id]);

  async function handleSubmit(e: SyntheticEvent) {
    e.preventDefault();
    if (!person || !id) return;
    setSaving(true);
    setError('');

    const data: PersonRequest = {
      firstName: user.isAdmin ? firstName : person.firstName,
      lastName: user.isAdmin ? lastName : person.lastName,
      startYear: user.isAdmin ? startYear : person.startYear,
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

    void navigate(`/person/${id}`);
  }

  async function handleDelete() {
    if (!id || !window.confirm('Supprimer définitivement ce profil ?')) return;
    const result = await deletePerson(Number(id));
    if (result.ok) void navigate('/tree');
    else setError(result.error.message);
  }

  if (loading) return <EditPersonPageSkeleton />;
  if (!person) {
    return (
      <div className="flex min-h-[40vh] items-center justify-center text-[14px] text-ink-3">
        {error || 'Personne introuvable.'}
      </div>
    );
  }

  const canEdit = user !== null && (user.isAdmin || user.person.id === person.id);
  if (!canEdit) return <Navigate to={`/person/${id}`} replace />;

  const color = promoColor(startYear);
  const canEditIdentity = user.isAdmin;
  const godFathers = sponsors.filter((s) => s.godChildId === person.id);
  const godChildren = sponsors.filter((s) => s.godFatherId === person.id);

  return (
    <div className="min-h-[calc(100vh-var(--header-height))] bg-bg">
      <form
        onSubmit={(e) => void handleSubmit(e)}
        className="mx-auto max-w-[980px] px-7 pb-20 pt-7"
      >
        <Breadcrumb
          items={[
            { label: 'Annuaire', onClick: () => void navigate('/tree') },
            { label: person.fullName, onClick: () => void navigate(`/person/${id}`) },
            { label: 'Modifier' },
          ]}
          className="mb-6"
        />

        {error && (
          <div className="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-700">
            {error}
          </div>
        )}

        <EditPersonHero
          person={person}
          firstName={firstName}
          lastName={lastName}
          startYear={startYear}
          biography={biography}
          description={description}
          picturePreview={picturePreview}
          canEditIdentity={canEditIdentity}
          onFirstNameChange={setFirstName}
          onLastNameChange={setLastName}
          onStartYearChange={setStartYear}
          onBiographyChange={setBiography}
          onDescriptionChange={setDescription}
          onFileChange={(file) => {
            setPendingPicture(file);
            setPicturePreview(URL.createObjectURL(file));
          }}
        />

        {/* Stats */}
        <div className="mb-5 grid grid-cols-3 gap-3">
          <StatCard label="Parrains" value={godFathers.length} accent={color} />
          <StatCard label="Fillots" value={godChildren.length} accent={color} />
          <StatCard label="Promo" value={startYear} />
        </div>

        {/* Parrainages */}
        <Card radius="xl" padding="md" className="mb-5">
          <h2 className="mb-4 text-[17px] font-semibold tracking-tight text-ink">Parrainages</h2>

          {godFathers.length > 0 && (
            <div className="mb-4">
              <p className="mb-2 text-[11px] font-semibold uppercase tracking-widest text-ink-3">
                Parrains
              </p>
              <div className="grid gap-2 sm:grid-cols-2">
                {godFathers.map((s) => (
                  <SponsorRow
                    key={s.id}
                    sponsor={s}
                    personId={person.id}
                    onDelete={(sid) => {
                      setSponsors((prev) => prev.filter((x) => x.id !== sid));
                    }}
                  />
                ))}
              </div>
            </div>
          )}

          {godChildren.length > 0 && (
            <div className="mb-4">
              <p className="mb-2 text-[11px] font-semibold uppercase tracking-widest text-ink-3">
                Fillots
              </p>
              <div className="grid gap-2 sm:grid-cols-2">
                {godChildren.map((s) => (
                  <SponsorRow
                    key={s.id}
                    sponsor={s}
                    personId={person.id}
                    onDelete={(sid) => {
                      setSponsors((prev) => prev.filter((x) => x.id !== sid));
                    }}
                  />
                ))}
              </div>
            </div>
          )}

          <AddSponsorForm
            personId={person.id}
            persons={allPersons}
            onAdded={(s) => {
              setSponsors((prev) => [...prev, s]);
            }}
          />
        </Card>

        {/* Zone admin */}
        {user.isAdmin && (
          <Card radius="xl" padding="md" className="mb-5">
            <h2 className="mb-4 text-[17px] font-semibold tracking-tight text-ink">
              Administration
            </h2>
            <div className="flex items-center justify-between">
              <p className="text-[13px] text-ink-3">
                Supprime définitivement ce profil et tous ses parrainages.
              </p>
              <Button type="button" variant="danger" size="sm" onClick={() => void handleDelete()}>
                Supprimer ce profil
              </Button>
            </div>
          </Card>
        )}

        {/* Barre d'actions */}
        <div className="flex items-center justify-between border-t border-line pt-5">
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onClick={() => void navigate(`/person/${id}`)}
          >
            ← Annuler
          </Button>
          <div className="flex items-center gap-3">
            <a
              href={`/api/persons/${person.id}/export`}
              className="inline-flex h-9 items-center gap-1.5 rounded-[9px] border border-line bg-surface px-4 text-sm text-ink-2 transition-colors hover:border-ink-2 hover:text-ink"
            >
              Exporter mes données
            </a>
            <Button type="submit" disabled={saving}>
              {saving ? 'Enregistrement…' : 'Enregistrer'}
            </Button>
          </div>
        </div>
      </form>
    </div>
  );
}
