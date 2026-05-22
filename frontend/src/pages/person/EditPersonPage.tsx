import { useMutation, useQueries, useQueryClient } from '@tanstack/react-query';
import { useCallback, useEffect, useRef, useState } from 'react';
import type { SyntheticEvent } from 'react';
import type { ReactNode } from 'react';
import { useNavigate, useParams } from 'react-router';
import {
  Avatar,
  Breadcrumb,
  Button,
  Card,
  ImagePickerModal,
  Input,
  Skeleton,
  StatCard,
  SuggestInput,
} from '../../components/ui';
import { useAuth } from '../../hooks/useAuth';
import { useNotification } from '../../hooks/useNotification';
import { deletePerson, updateAccount, updatePerson, uploadPicture } from '../../lib/api/persons';
import { createSponsor, deleteSponsor, updateSponsor } from '../../lib/api/sponsors';
import { personQueries } from '../../lib/queries';
import { promoColor } from '../../lib/colors';
import { SPONSOR_TYPE_ICONS, SPONSOR_TYPE_LABELS } from '../../lib/sponsorTypes';
import type {
  Association,
  AssociationRequest,
  Filiere,
  FiliereRequest,
  Person,
  PersonRequest,
} from '../../types/person';
import type { Sponsor, SponsorType } from '../../types/sponsor';
import { getFilieres } from '../../lib/api/filieres';
import { getSchools } from '../../lib/api/schools';
import { getAssociations } from '../../lib/api/associations';

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

// ── Formulaire ajout parrainage ───────────────────────────────────────────────

function AddSponsorForm({
  personId,
  onAdded,
}: {
  personId: number;
  onAdded: (s: Sponsor) => void;
}) {
  const { notify } = useNotification();
  const queryClient = useQueryClient();
  const [role, setRole] = useState<'godChild' | 'godFather'>('godChild');
  const [otherPerson, setOtherPerson] = useState<Person | null>(null);
  const [otherQuery, setOtherQuery] = useState('');
  const [type, setType] = useState<SponsorType>('CLASSIC');
  const [date, setDate] = useState('');
  const [description, setDescription] = useState('');

  const searchOtherPersons = useCallback(
    async (q: string): Promise<Person[]> => {
      const data = await queryClient.fetchQuery(personQueries.search(q, 20));
      return data.filter((p) => p.id !== personId);
    },
    [queryClient, personId],
  );

  const { mutate, isPending: saving } = useMutation({
    mutationFn: createSponsor,
    onSuccess: (result) => {
      if (!result.ok) {
        notify('error', result.error.message);
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
      notify('success', 'Parrainage ajouté');
      setOtherPerson(null);
      setOtherQuery('');
      setDate('');
      setDescription('');
      void queryClient.invalidateQueries({ queryKey: personQueries.detail(personId).queryKey });
    },
  });

  function handleAdd() {
    if (!otherPerson) {
      notify('warning', 'Sélectionnez une personne');
      return;
    }
    mutate({
      godFatherId: role === 'godFather' ? personId : otherPerson.id,
      godChildId: role === 'godChild' ? personId : otherPerson.id,
      type,
      date: date || null,
      description: description || null,
    });
  }

  const TYPE_OPTIONS: { value: SponsorType; label: string }[] = [
    { value: 'CLASSIC', label: 'IUT' },
    { value: 'HEART', label: 'De cœur' },
    { value: 'FALUCHE', label: 'Faluchard' },
    { value: 'UNKNOWN', label: 'Inconnu' },
  ];

  return (
    <div className="rounded-xl border border-dashed border-line bg-bg p-4">
      <p className="mb-3 text-[12px] font-semibold uppercase tracking-widest text-ink-3">
        Ajouter un parrainage
      </p>

      <div className="grid gap-3 sm:grid-cols-2">
        {/* Rôle */}
        <div>
          <FieldLabel>Mon rôle</FieldLabel>
          <div className="flex flex-wrap gap-2">
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
          <SuggestInput<Person>
            value={otherQuery}
            onChange={(v) => {
              setOtherQuery(v);
              if (otherPerson && v !== otherPerson.fullName) setOtherPerson(null);
            }}
            search={searchOtherPersons}
            getLabel={(p) => p.fullName}
            getKey={(p) => p.id}
            renderItem={(p, active) => (
              <div className="flex items-center gap-2.5">
                <Avatar person={p} size={24} square />
                <span className="font-medium">{p.fullName}</span>
                <span className={`ml-auto text-[11px] ${active ? 'text-ink-3' : 'text-ink-4'}`}>
                  Promo {p.startYear}
                </span>
              </div>
            )}
            onPick={(p) => {
              setOtherPerson(p);
              setOtherQuery(p.fullName);
            }}
            placeholder="Rechercher une personne…"
          />
        </div>

        {/* Type */}
        <div>
          <FieldLabel>Type</FieldLabel>
          <div className="flex flex-wrap gap-2">
            {TYPE_OPTIONS.map((o) => (
              <button
                key={o.value}
                type="button"
                onClick={() => {
                  setType(o.value);
                }}
                className={`rounded-[9px] border px-2 py-1.5 text-[12px] font-medium transition-colors cursor-pointer ${
                  type === o.value
                    ? 'border-ink bg-ink text-white'
                    : 'border-line bg-surface text-ink-2 hover:border-ink-3'
                }`}
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
            data-testid="sponsor-date"
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
          onClick={() => {
            handleAdd();
          }}
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
  onUpdate,
}: {
  sponsor: Sponsor;
  personId: number;
  onDelete: (id: number) => void;
  onUpdate: (updated: Sponsor) => void;
}) {
  const isGodFather = sponsor.godFatherId === personId;
  const relatedName = isGodFather ? sponsor.godChildName : sponsor.godFatherName;
  const role = isGodFather ? 'Fillot' : 'Parrain';
  const startYear = new Date(sponsor.date ?? '').getFullYear() || 2020;
  const color = promoColor(startYear);

  const { notify } = useNotification();
  const queryClient = useQueryClient();

  const [editing, setEditing] = useState(false);
  const [editType, setEditType] = useState<SponsorType>(sponsor.type);
  const [editDate, setEditDate] = useState(sponsor.date ?? '');
  const [editDescription, setEditDescription] = useState(sponsor.description ?? '');

  const TYPE_OPTIONS: { value: SponsorType; label: string }[] = [
    { value: 'CLASSIC', label: 'IUT' },
    { value: 'HEART', label: 'De cœur' },
    { value: 'FALUCHE', label: 'Faluchard' },
    { value: 'UNKNOWN', label: 'Inconnu' },
  ];

  const { mutate: doDelete, isPending: deleting } = useMutation({
    mutationFn: () => deleteSponsor(sponsor.id),
    onSuccess: (result) => {
      if (!result.ok) {
        notify('error', result.error.message);
        return;
      }
      onDelete(sponsor.id);
      notify('success', 'Parrainage supprimé');
      void queryClient.invalidateQueries({ queryKey: personQueries.detail(personId).queryKey });
    },
  });

  const { mutate: doSaveEdit, isPending: saving } = useMutation({
    mutationFn: () =>
      updateSponsor(sponsor.id, {
        godFatherId: sponsor.godFatherId,
        godChildId: sponsor.godChildId,
        type: editType,
        date: editDate || null,
        description: editDescription || null,
      }),
    onSuccess: (result) => {
      if (!result.ok) {
        notify('error', result.error.message);
        return;
      }
      onUpdate({ ...sponsor, type: result.data.type, date: result.data.date });
      notify('success', 'Parrainage mis à jour');
      setEditing(false);
      void queryClient.invalidateQueries({ queryKey: personQueries.detail(personId).queryKey });
    },
  });

  function handleEditOpen() {
    setEditType(sponsor.type);
    setEditDate(sponsor.date ?? '');
    setEditDescription(sponsor.description ?? '');
    setEditing(true);
  }

  if (editing) {
    return (
      <div
        data-testid={`sponsor-row-${String(sponsor.id)}`}
        className="rounded-xl border border-ink-3 bg-surface p-3"
      >
        <p className="mb-2 text-[11px] font-semibold uppercase tracking-widest text-ink-3">
          Modifier · {relatedName}
        </p>
        <div className="mb-3 flex flex-wrap gap-2">
          {TYPE_OPTIONS.map((o) => (
            <button
              key={o.value}
              type="button"
              onClick={() => {
                setEditType(o.value);
              }}
              className={`rounded-[9px] border px-2.5 py-1 text-[12px] font-medium transition-colors cursor-pointer ${
                editType === o.value
                  ? 'border-ink bg-ink text-white'
                  : 'border-line bg-bg text-ink-2 hover:border-ink-3'
              }`}
            >
              {SPONSOR_TYPE_ICONS[o.value]} {o.label}
            </button>
          ))}
        </div>
        <Input
          type="date"
          value={editDate}
          onChange={(e) => {
            setEditDate(e.target.value);
          }}
          className="mb-3"
        />
        <div className="mb-3">
          <FieldLabel>Description (optionnel)</FieldLabel>
          <textarea
            value={editDescription}
            onChange={(e) => {
              setEditDescription(e.target.value);
            }}
            rows={2}
            placeholder="Anecdote, contexte…"
            className="w-full resize-none rounded-[9px] border border-line bg-bg px-3.5 py-2.5 text-sm text-ink outline-none transition-colors placeholder:text-ink-4 focus:border-ink-2"
          />
        </div>
        <div className="flex justify-end gap-2">
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onClick={() => {
              setEditing(false);
            }}
          >
            Annuler
          </Button>
          <Button
            type="button"
            size="sm"
            disabled={saving}
            onClick={() => {
              doSaveEdit();
            }}
          >
            {saving ? 'Enregistrement…' : 'Enregistrer'}
          </Button>
        </div>
      </div>
    );
  }

  return (
    <div
      data-testid={`sponsor-row-${String(sponsor.id)}`}
      className="flex items-center gap-3 rounded-xl border border-line bg-surface p-3"
    >
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
      {/* Bouton éditer */}
      <Button
        type="button"
        variant="ghost"
        size="sm"
        icon={
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
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
          </svg>
        }
        onClick={handleEditOpen}
        className="shrink-0 text-ink-4"
        title="Modifier ce parrainage"
      />
      {/* Bouton supprimer */}
      <Button
        type="button"
        variant="ghost"
        confirmVariant="danger"
        size="sm"
        confirm
        loading={deleting}
        title="Supprimer ce parrainage"
        icon={
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
        }
        onClick={() => {
          doDelete();
        }}
        className="shrink-0 text-ink-4"
      />
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
  onOpenPicker,
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
  onOpenPicker: () => void;
}) {
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
    <div className="relative mb-5 overflow-hidden rounded-2xl border border-line bg-surface p-4 sm:p-8">
      <div
        className="pointer-events-none absolute inset-0"
        style={{ background: `linear-gradient(135deg, ${color}12 0%, transparent 55%)` }}
      />
      <div className="relative flex flex-col gap-5 sm:flex-row sm:flex-wrap sm:items-start sm:gap-7">
        {/* Avatar cliquable */}
        <div className="flex flex-col items-center sm:items-start">
          <FieldLabel>Photo</FieldLabel>
          <button
            type="button"
            className="relative h-24 w-24 cursor-pointer overflow-hidden rounded-[18px] focus:outline-none sm:h-[140px] sm:w-[140px]"
            style={{ boxShadow: `0 0 0 3px ${color}, 0 8px 24px ${color}30` }}
            onClick={onOpenPicker}
            onMouseEnter={() => {
              setAvatarHover(true);
            }}
            onMouseLeave={() => {
              setAvatarHover(false);
            }}
            title="Changer la photo"
          >
            <Avatar person={displayPerson} fill imageSize="full" />
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
          {picturePreview && (
            <p className="mt-2 flex items-center gap-1 rounded-md bg-amber-500/10 px-2 py-1 text-[11px] font-medium text-amber-500">
              <svg
                width="11"
                height="11"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2.5"
                strokeLinecap="round"
                strokeLinejoin="round"
              >
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
              </svg>
              Non sauvegardé
            </p>
          )}
        </div>

        {/* Champs */}
        <div className="min-w-0 flex-1">
          <div className="mb-4 flex flex-wrap gap-3">
            {canEditIdentity && (
              <>
                <div className="flex-1 min-w-[120px]">
                  <FieldLabel>Prénom</FieldLabel>
                  <Input
                    value={firstName}
                    onChange={(e) => {
                      onFirstNameChange(e.target.value);
                    }}
                    placeholder="Prénom"
                  />
                </div>
                <div className="flex-1 min-w-[120px]">
                  <FieldLabel>Nom</FieldLabel>
                  <Input
                    value={lastName}
                    onChange={(e) => {
                      onLastNameChange(e.target.value);
                    }}
                    placeholder="Nom"
                  />
                </div>
              </>
            )}
            {!canEditIdentity && (
              <div>
                <FieldLabel>Nom</FieldLabel>
                <p className="text-[20px] font-semibold leading-tight tracking-tight text-ink sm:text-[22px]">
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
                data-testid="person-start-year"
              />
            </div>
          </div>

          <div className="space-y-2">
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

// -- Composant d'édition de filieres --------------------------------------------

function FilieresEditor({
  filieres,
  allFilieres,
  allSchools,
  onChange,
}: {
  filieres: Filiere[];
  allFilieres: string[];
  allSchools: string[];
  onChange: (filieres: Filiere[]) => void;
}) {
  function addRow() {
    onChange([
      ...filieres,
      {
        _id: crypto.randomUUID(),
        name: '',
        color: null,
        startYear: new Date().getFullYear(),
        endYear: null,
        schoolName: null,
        schoolLogoUrl: null,
        diplomaName: null,
      },
    ]);
  }

  function removeRow(index: number) {
    onChange(filieres.filter((_, i) => i !== index));
  }

  function updateRow(index: number, patch: Partial<Filiere>) {
    onChange(filieres.map((f, i) => (i === index ? { ...f, ...patch } : f)));
  }

  const trashIcon = (
    <svg
      width="14"
      height="14"
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
  );

  return (
    <div>
      <div className="space-y-3 sm:space-y-2">
        {filieres.map((f, i) => (
          // Mobile : carte avec labels toujours visibles
          // Desktop (sm+) : flex row — sm:contents sur le wrapper grid rend les enfants
          // transparents pour le flex parent
          <div
            key={f._id ?? i}
            className="rounded-xl border border-line bg-surface p-3 sm:flex sm:items-end sm:gap-2 sm:rounded-none sm:border-0 sm:bg-transparent sm:p-0"
          >
            {/* En-tête mobile : numéro + bouton supprimer */}
            <div className="mb-2 flex items-center justify-between sm:hidden">
              <span className="text-[10.5px] font-semibold uppercase tracking-widest text-ink-3">
                Filière {i + 1}
              </span>
              <Button
                type="button"
                variant="ghost"
                size="sm"
                onClick={() => {
                  removeRow(i);
                }}
                className="text-ink-4"
                data-testid="filiere-remove"
                icon={trashIcon}
              />
            </div>

            <div className="grid grid-cols-2 gap-2 sm:contents">
              <div className="col-span-2 sm:flex-1">
                <p className="mb-1 text-[10.5px] font-semibold uppercase tracking-widest text-ink-3 sm:hidden">
                  Filière
                </p>
                {i === 0 && (
                  <p className="mb-1 hidden text-[10.5px] font-semibold uppercase tracking-widest text-ink-3 sm:block">
                    Filière
                  </p>
                )}
                <SuggestInput
                  value={f.name}
                  onChange={(v) => {
                    updateRow(i, { name: v });
                  }}
                  suggestions={allFilieres}
                  placeholder="Filière"
                />
              </div>

              <div className="col-span-2 sm:flex-1">
                <p className="mb-1 text-[10.5px] font-semibold uppercase tracking-widest text-ink-3 sm:hidden">
                  École
                </p>
                {i === 0 && (
                  <p className="mb-1 hidden text-[10.5px] font-semibold uppercase tracking-widest text-ink-3 sm:block">
                    École
                  </p>
                )}
                <SuggestInput
                  value={f.schoolName ?? ''}
                  onChange={(v) => {
                    updateRow(i, { schoolName: v || null });
                  }}
                  suggestions={allSchools}
                  placeholder="École (optionnel)"
                />
              </div>

              <div className="sm:w-24">
                <p className="mb-1 text-[10.5px] font-semibold uppercase tracking-widest text-ink-3 sm:hidden">
                  Début
                </p>
                {i === 0 && (
                  <p className="mb-1 hidden text-[10.5px] font-semibold uppercase tracking-widest text-ink-3 sm:block">
                    Début
                  </p>
                )}
                <Input
                  type="number"
                  value={f.startYear}
                  onChange={(e) => {
                    updateRow(i, { startYear: Number(e.target.value) });
                  }}
                  min={1900}
                  max={2100}
                />
              </div>

              <div className="sm:w-24">
                <p className="mb-1 text-[10.5px] font-semibold uppercase tracking-widest text-ink-3 sm:hidden">
                  Fin
                </p>
                {i === 0 && (
                  <p className="mb-1 hidden text-[10.5px] font-semibold uppercase tracking-widest text-ink-3 sm:block">
                    Fin
                  </p>
                )}
                <Input
                  type="number"
                  value={f.endYear ?? ''}
                  onChange={(e) => {
                    updateRow(i, { endYear: e.target.value ? Number(e.target.value) : null });
                  }}
                  min={1900}
                  max={2100}
                  placeholder="—"
                />
              </div>

              <div className="col-span-2 sm:flex-1">
                <p className="mb-1 text-[10.5px] font-semibold uppercase tracking-widest text-ink-3 sm:hidden">
                  Diplôme
                </p>
                {i === 0 && (
                  <p className="mb-1 hidden text-[10.5px] font-semibold uppercase tracking-widest text-ink-3 sm:block">
                    Diplôme
                  </p>
                )}
                <Input
                  value={f.diplomaName ?? ''}
                  onChange={(e) => {
                    updateRow(i, { diplomaName: e.target.value || null });
                  }}
                  placeholder="Nom du diplôme (optionnel)"
                />
              </div>
            </div>

            {/* Bouton supprimer — desktop seulement */}
            <div className="hidden sm:block">
              {i === 0 && <div className="mb-1 h-[14px]" />}
              <Button
                type="button"
                variant="ghost"
                size="sm"
                onClick={() => {
                  removeRow(i);
                }}
                className="text-ink-4"
                data-testid="filiere-remove"
                icon={trashIcon}
              />
            </div>
          </div>
        ))}
      </div>

      <Button type="button" variant="ghost" size="sm" className="mt-2 text-ink-3" onClick={addRow}>
        + Ajouter une filière
      </Button>
    </div>
  );
}

// -- Composant d'édition des associations ---------------------------------------

function AssociationsEditor({
  associations,
  allAssociations,
  onChange,
}: {
  associations: Association[];
  allAssociations: string[];
  onChange: (associations: Association[]) => void;
}) {
  function addRow() {
    onChange([
      ...associations,
      {
        _id: crypto.randomUUID(),
        name: '',
        logoUrl: null,
        poste: '',
        startDate: null,
        endDate: null,
      },
    ]);
  }

  function removeRow(index: number) {
    onChange(associations.filter((_, i) => i !== index));
  }

  function updateRow(index: number, patch: Partial<Association>) {
    onChange(associations.map((a, i) => (i === index ? { ...a, ...patch } : a)));
  }

  const trashIcon = (
    <svg
      width="14"
      height="14"
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
  );

  return (
    <div>
      <div className="space-y-3 sm:space-y-2">
        {associations.map((a, i) => (
          <div
            key={a._id ?? i}
            className="rounded-xl border border-line bg-surface p-3 sm:flex sm:items-end sm:gap-2 sm:rounded-none sm:border-0 sm:bg-transparent sm:p-0"
          >
            {/* En-tête mobile */}
            <div className="mb-2 flex items-center justify-between sm:hidden">
              <span className="text-[10.5px] font-semibold uppercase tracking-widest text-ink-3">
                Association {i + 1}
              </span>
              <Button
                type="button"
                variant="ghost"
                size="sm"
                onClick={() => {
                  removeRow(i);
                }}
                className="text-ink-4"
                data-testid="association-remove"
                icon={trashIcon}
              />
            </div>

            <div className="grid grid-cols-2 gap-2 sm:contents">
              <div className="col-span-2 sm:flex-1">
                <p className="mb-1 text-[10.5px] font-semibold uppercase tracking-widest text-ink-3 sm:hidden">
                  Association
                </p>
                {i === 0 && (
                  <p className="mb-1 hidden text-[10.5px] font-semibold uppercase tracking-widest text-ink-3 sm:block">
                    Association
                  </p>
                )}
                <SuggestInput
                  value={a.name}
                  onChange={(v) => {
                    updateRow(i, { name: v });
                  }}
                  suggestions={allAssociations}
                  placeholder="Association"
                />
              </div>

              <div className="col-span-2 sm:flex-1">
                <p className="mb-1 text-[10.5px] font-semibold uppercase tracking-widest text-ink-3 sm:hidden">
                  Poste
                </p>
                {i === 0 && (
                  <p className="mb-1 hidden text-[10.5px] font-semibold uppercase tracking-widest text-ink-3 sm:block">
                    Poste
                  </p>
                )}
                <Input
                  value={a.poste}
                  onChange={(e) => {
                    updateRow(i, { poste: e.target.value });
                  }}
                  placeholder="Poste (ex : Président)"
                />
              </div>

              <div className="sm:w-36">
                <p className="mb-1 text-[10.5px] font-semibold uppercase tracking-widest text-ink-3 sm:hidden">
                  Début
                </p>
                {i === 0 && (
                  <p className="mb-1 hidden text-[10.5px] font-semibold uppercase tracking-widest text-ink-3 sm:block">
                    Début
                  </p>
                )}
                <Input
                  type="date"
                  value={a.startDate ?? ''}
                  onChange={(e) => {
                    updateRow(i, { startDate: e.target.value || null });
                  }}
                />
              </div>

              <div className="sm:w-36">
                <p className="mb-1 text-[10.5px] font-semibold uppercase tracking-widest text-ink-3 sm:hidden">
                  Fin
                </p>
                {i === 0 && (
                  <p className="mb-1 hidden text-[10.5px] font-semibold uppercase tracking-widest text-ink-3 sm:block">
                    Fin
                  </p>
                )}
                <Input
                  type="date"
                  value={a.endDate ?? ''}
                  onChange={(e) => {
                    updateRow(i, { endDate: e.target.value || null });
                  }}
                />
              </div>
            </div>

            {/* Bouton supprimer — desktop seulement */}
            <div className="hidden sm:block">
              {i === 0 && <div className="mb-1 h-[14px]" />}
              <Button
                type="button"
                variant="ghost"
                size="sm"
                onClick={() => {
                  removeRow(i);
                }}
                className="text-ink-4"
                data-testid="association-remove"
                icon={trashIcon}
              />
            </div>
          </div>
        ))}
      </div>

      <Button type="button" variant="ghost" size="sm" className="mt-2 text-ink-3" onClick={addRow}>
        + Ajouter une association
      </Button>
    </div>
  );
}

// ── Page ──────────────────────────────────────────────────────────────────────

export function EditPersonPage() {
  const { id } = useParams<{ id: string }>();
  const { user, refreshUser } = useAuth();
  const navigate = useNavigate();
  const { notify } = useNotification();

  const personId = Number(id);
  const queryClient = useQueryClient();

  const [personQuery, accountQuery] = useQueries({
    queries: [
      { ...personQueries.detail(personId), enabled: !!id },
      { ...personQueries.account(personId), enabled: !!id },
    ],
  });

  const person = personQuery.data ?? null;
  const loading = personQuery.isLoading;

  const initialized = useRef(false);
  const [saving, setSaving] = useState(false);

  // null = pas de compte ; undefined = valeur non encore modifiée par l'utilisateur (on lit la query)
  const [accountEmailDraft, setAccountEmailDraft] = useState<string | null | undefined>(undefined);
  const accountEmail =
    accountEmailDraft !== undefined ? accountEmailDraft : (accountQuery.data?.email ?? null);
  const [accountCurrentPassword, setAccountCurrentPassword] = useState('');
  const [accountNewPassword, setAccountNewPassword] = useState('');
  const [accountConfirmPassword, setAccountConfirmPassword] = useState('');
  const [savingAccount, setSavingAccount] = useState(false);

  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [startYear, setStartYear] = useState(new Date().getFullYear());
  const [biography, setBiography] = useState('');
  const [description, setDescription] = useState('');
  const [pendingPicture, setPendingPicture] = useState<File | null>(null);
  const [picturePreview, setPicturePreview] = useState<string | null>(null);
  const [pickerOpen, setPickerOpen] = useState(false);
  const [sponsors, setSponsors] = useState<Sponsor[]>([]);
  const [filieres, setFilieres] = useState<Filiere[]>([]);
  const [allFilieres, setAllFilieres] = useState<string[]>([]);
  const [allSchools, setAllSchools] = useState<string[]>([]);
  const [associations, setAssociations] = useState<Association[]>([]);
  const [allAssociations, setAllAssociations] = useState<string[]>([]);

  useEffect(() => {
    if (!person || initialized.current) return;
    initialized.current = true;
    setFirstName(person.firstName);
    setLastName(person.lastName);
    setStartYear(person.startYear);
    setBiography(person.biography ?? '');
    setDescription(person.description ?? '');
    setSponsors([...person.godFathers, ...person.godChildren]);
    setFilieres(
      person.filieres.map((f) => ({
        ...f,
        _id: crypto.randomUUID(),
        diplomaName: f.diplomaName ?? null,
      })),
    );
    setAssociations(
      person.associations.map((a) => ({
        ...a,
        _id: crypto.randomUUID(),
        startDate: a.startDate ?? null,
        endDate: a.endDate ?? null,
      })),
    );
  }, [person]);

  useEffect(() => {
    initialized.current = false;
  }, [personId]);

  useEffect(() => {
    void getFilieres().then((result) => {
      if (result.ok) setAllFilieres(result.data);
      else notify('error', 'Impossible de charger les filières');
    });
    void getSchools().then((result) => {
      if (result.ok) setAllSchools(result.data);
    });
    void getAssociations().then((result) => {
      if (result.ok) setAllAssociations(result.data);
    });
  }, [notify]);

  async function handleSubmit(e: SyntheticEvent) {
    e.preventDefault();
    if (!person || !id) return;

    for (const f of filieres) {
      if (!f.name.trim()) {
        notify('error', 'Le nom de la filière ne peut pas être vide');
        return;
      }
      if (f.endYear !== null && f.endYear <= f.startYear) {
        notify(
          'error',
          `La filière "${f.name}" : l'année de fin doit être supérieure à l'année de début`,
        );
        return;
      }
    }

    for (const a of associations) {
      if (!a.name.trim()) {
        notify('error', "Le nom de l'association ne peut pas être vide");
        return;
      }
      if (!a.poste.trim()) {
        notify('error', `L'association "${a.name}" : le poste ne peut pas être vide`);
        return;
      }
    }

    setSaving(true);

    const data: PersonRequest = {
      firstName: user.isAdmin ? firstName : person.firstName,
      lastName: user.isAdmin ? lastName : person.lastName,
      startYear: user.isAdmin ? startYear : person.startYear,
      biography: biography || null,
      description: description || null,
      filieres: filieres.map(
        ({ name, startYear: sy, endYear, schoolName, diplomaName }): FiliereRequest => ({
          name,
          startYear: sy,
          endYear,
          schoolName,
          diplomaName,
        }),
      ),
      associations: associations.map(
        ({ name, poste, startDate, endDate }): AssociationRequest => ({
          name,
          poste,
          startDate,
          endDate,
        }),
      ),
    };

    const updateResult = await updatePerson(Number(id), data);
    if (!updateResult.ok) {
      notify('error', updateResult.error.message);
      setSaving(false);
      return;
    }

    if (pendingPicture) {
      const picResult = await uploadPicture(Number(id), pendingPicture);
      if (!picResult.ok) {
        notify('error', picResult.error.message);
        setSaving(false);
        return;
      }
    }

    queryClient.removeQueries({ queryKey: ['tree'] });
    await queryClient.invalidateQueries({ queryKey: ['persons'] });
    if (pendingPicture && user.person.id === personId) await refreshUser();
    notify('success', 'Profil mis à jour');
    void navigate(`/person/${id}`);
  }

  async function handleSaveAccount() {
    if (!person || !id) return;
    if (accountNewPassword && accountNewPassword !== accountConfirmPassword) {
      notify('error', 'Les mots de passe ne correspondent pas');
      return;
    }
    setSavingAccount(true);

    const isOwnProfile = user.person.id === person.id;

    const result = await updateAccount(person.id, {
      email: accountEmail !== '' ? accountEmail : undefined,
      currentPassword: isOwnProfile ? accountCurrentPassword || undefined : undefined,
      newPassword: accountNewPassword || undefined,
    });

    if (!result.ok) {
      notify('error', result.error.message);
      setSavingAccount(false);
      return;
    }

    setAccountEmailDraft(undefined);
    setAccountCurrentPassword('');
    setAccountNewPassword('');
    setAccountConfirmPassword('');
    notify('success', 'Compte mis à jour avec succès');
    setSavingAccount(false);
  }

  const [deleting, setDeleting] = useState(false);

  function handleDelete() {
    if (!id) return;
    setDeleting(true);
    void deletePerson(Number(id)).then((result) => {
      if (result.ok) void navigate('/tree');
      else {
        notify('error', result.error.message);
        setDeleting(false);
      }
    });
  }

  if (loading) return <EditPersonPageSkeleton />;
  if (!user) return null;
  if (personQuery.isError || !person) {
    return (
      <div className="flex min-h-[40vh] items-center justify-center text-[14px] text-ink-3">
        Personne introuvable.
      </div>
    );
  }

  const color = promoColor(startYear);
  const canEditIdentity = user.isAdmin;
  const godFathers = sponsors.filter((s) => s.godChildId === person.id);
  const godChildren = sponsors.filter((s) => s.godFatherId === person.id);

  return (
    <div className="min-h-[calc(100vh-var(--header-height))] bg-bg">
      <form
        onSubmit={(e) => void handleSubmit(e)}
        className="mx-auto max-w-[980px] px-4 pb-20 pt-5 sm:px-7 sm:pt-7"
      >
        <Breadcrumb
          items={[
            { label: 'Annuaire', onClick: () => void navigate('/tree') },
            { label: person.fullName, onClick: () => void navigate(`/person/${id}`) },
            { label: 'Modifier' },
          ]}
          className="mb-6"
        />

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
          onOpenPicker={() => {
            setPickerOpen(true);
          }}
        />

        <ImagePickerModal
          open={pickerOpen}
          onClose={() => {
            setPickerOpen(false);
          }}
          onConfirm={(file, preview) => {
            setPendingPicture(file);
            setPicturePreview(preview);
            notify('info', "Photo sélectionnée — cliquez sur Enregistrer pour l'appliquer");
          }}
        />

        {/* Stats */}
        <div className="mb-5 grid grid-cols-3 gap-3">
          <StatCard label="Parrains" value={godFathers.length} accent={color} />
          <StatCard label="Fillots" value={godChildren.length} accent={color} />
          <StatCard label="Promo" value={startYear} />
        </div>

        {/* Filières */}
        <Card radius="xl" padding="md" className="mb-5">
          <h2 className="mb-4 text-[17px] font-semibold tracking-tight text-ink">Filières</h2>
          <FilieresEditor
            filieres={filieres}
            allFilieres={allFilieres}
            allSchools={allSchools}
            onChange={setFilieres}
          />
        </Card>

        {/* Associations */}
        <Card radius="xl" padding="md" className="mb-5">
          <h2 className="mb-4 text-[17px] font-semibold tracking-tight text-ink">Associations</h2>
          <AssociationsEditor
            associations={associations}
            allAssociations={allAssociations}
            onChange={setAssociations}
          />
        </Card>

        {/* Parrainages */}
        <Card radius="xl" padding="md" className="mb-5">
          <h2 className="mb-4 text-[17px] font-semibold tracking-tight text-ink">Parrainages</h2>

          {godFathers.length > 0 && (
            <div className="mb-4">
              <p className="mb-2 text-[11px] font-semibold uppercase tracking-widest text-ink-3">
                Parrains
              </p>
              <div className="grid gap-2 sm:grid-cols-2 sm:items-start">
                {godFathers.map((s) => (
                  <SponsorRow
                    key={s.id}
                    sponsor={s}
                    personId={person.id}
                    onDelete={(sid) => {
                      setSponsors((prev) => prev.filter((x) => x.id !== sid));
                    }}
                    onUpdate={(updated) => {
                      setSponsors((prev) => prev.map((x) => (x.id === updated.id ? updated : x)));
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
              <div className="grid gap-2 sm:grid-cols-2 sm:items-start">
                {godChildren.map((s) => (
                  <SponsorRow
                    key={s.id}
                    sponsor={s}
                    personId={person.id}
                    onDelete={(sid) => {
                      setSponsors((prev) => prev.filter((x) => x.id !== sid));
                    }}
                    onUpdate={(updated) => {
                      setSponsors((prev) => prev.map((x) => (x.id === updated.id ? updated : x)));
                    }}
                  />
                ))}
              </div>
            </div>
          )}

          <AddSponsorForm
            personId={person.id}
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
              <Button
                type="button"
                variant="danger"
                size="sm"
                confirm
                loading={deleting}
                onClick={handleDelete}
              >
                Supprimer ce profil
              </Button>
            </div>
          </Card>
        )}

        {/* Compte */}
        {accountEmail !== null && (
          <Card radius="xl" padding="md" className="mb-5">
            <h2 className="mb-4 text-[17px] font-semibold tracking-tight text-ink">Compte</h2>

            <div className="grid gap-3 sm:grid-cols-2">
              <div className="sm:col-span-2">
                <FieldLabel>Nouvelle adresse e-mail universitaire</FieldLabel>
                <Input
                  type="email"
                  value={accountEmail}
                  onChange={(e) => {
                    setAccountEmailDraft(e.target.value);
                  }}
                  placeholder="nouvelle@adresse.fr"
                />
              </div>

              {user.person.id === person.id && (
                <div className="sm:col-span-2">
                  <FieldLabel>Mot de passe actuel</FieldLabel>
                  <Input
                    type="password"
                    value={accountCurrentPassword}
                    onChange={(e) => {
                      setAccountCurrentPassword(e.target.value);
                    }}
                    placeholder="Requis pour modifier e-mail ou mot de passe"
                  />
                </div>
              )}

              <div>
                <FieldLabel>Nouveau mot de passe</FieldLabel>
                <Input
                  type="password"
                  value={accountNewPassword}
                  onChange={(e) => {
                    setAccountNewPassword(e.target.value);
                  }}
                  placeholder="Laisser vide pour ne pas changer"
                />
              </div>

              <div>
                <FieldLabel>Confirmer le mot de passe</FieldLabel>
                <Input
                  type="password"
                  value={accountConfirmPassword}
                  onChange={(e) => {
                    setAccountConfirmPassword(e.target.value);
                  }}
                  placeholder="Répéter le nouveau mot de passe"
                />
              </div>
            </div>

            <div className="mt-4 flex justify-end">
              <Button
                type="button"
                size="sm"
                disabled={savingAccount || (!accountEmail && !accountNewPassword)}
                onClick={() => void handleSaveAccount()}
              >
                {savingAccount ? 'Enregistrement…' : 'Mettre à jour le compte'}
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
            <Button type="submit" disabled={saving}>
              {saving ? 'Enregistrement…' : 'Enregistrer'}
            </Button>
          </div>
        </div>
      </form>
    </div>
  );
}
