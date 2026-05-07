import { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router';
import { Avatar, Breadcrumb, Card, Skeleton, StatCard } from '../../components/ui';
import { useAuth } from '../../hooks/useAuth';
import { getPerson } from '../../lib/api/persons';
import { promoColor } from '../../lib/colors';
import { SPONSOR_TYPE_ICONS, SPONSOR_TYPE_LABELS } from '../../lib/sponsorTypes';
import type { Characteristic, Person } from '../../types/person';
import type { SponsorSummary } from '../../types/sponsor';
import { FamilyGraph } from './FamilyGraph';

// ── Sub-components ────────────────────────────────────────────────────────────

function FamilyChip({ sponsor, personId }: { sponsor: SponsorSummary; personId: number }) {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const isGodFather = sponsor.godFatherId === personId;
  const relatedId = isGodFather ? sponsor.godChildId : sponsor.godFatherId;
  const relatedName = isGodFather ? sponsor.godChildName : sponsor.godFatherName;
  const role = isGodFather ? 'Fillot' : 'Parrain';
  const startYear = new Date(sponsor.date ?? '').getFullYear() || 2020;
  const color = promoColor(startYear);

  const handleClick = () => {
    setLoading(true);
    void navigate(`/person/${relatedId}`);
  };

  return (
    <div
      role="link"
      tabIndex={0}
      onClick={handleClick}
      onKeyDown={(e) => {
        if (e.key === 'Enter') handleClick();
      }}
      className="flex cursor-pointer items-center gap-3 rounded-xl border border-line bg-surface p-4 transition-all hover:-translate-y-px hover:border-ink-4 hover:shadow-md"
    >
      <div className="relative shrink-0">
        <Avatar
          person={{
            firstName: relatedName.split(' ')[0] ?? '',
            lastName: relatedName.split(' ').slice(1).join(' '),
            fullName: relatedName,
            picture: null,
            startYear,
          }}
          size={40}
          square
        />
        {loading && (
          <div className="absolute inset-0 flex items-center justify-center rounded-lg bg-black/35">
            <div className="h-4 w-4 animate-spin rounded-full border-2 border-white/35 border-t-white" />
          </div>
        )}
      </div>
      <div className="min-w-0 flex-1">
        <p className="truncate text-[13px] font-medium text-ink">{relatedName}</p>
        <p className="text-[11.5px]" style={{ color }}>
          {role} · Parrainage {SPONSOR_TYPE_LABELS[sponsor.type]}
        </p>
      </div>
      <span className="shrink-0 text-base">{SPONSOR_TYPE_ICONS[sponsor.type]}</span>
    </div>
  );
}

function CharacteristicChip({ characteristic }: { characteristic: Characteristic }) {
  const icon = characteristic.typeImage
    ? `/images/icons/${characteristic.typeImage}`
    : characteristic.typeUrl
      ? `https://www.google.com/s2/favicons?domain=${new URL(characteristic.typeUrl).hostname}&sz=16`
      : null;

  const inner = (
    <span className="flex items-center gap-2 rounded-lg border border-line bg-surface px-3 py-1.5 text-[12.5px] transition-colors hover:border-ink-3">
      {icon && (
        <img
          src={icon}
          alt=""
          width={14}
          height={14}
          className="shrink-0 opacity-60"
          style={{ filter: 'brightness(0)' }}
          onError={(e) => {
            e.currentTarget.style.display = 'none';
          }}
        />
      )}
      <span className="text-ink-3">{characteristic.typeTitle}</span>
      <span className="font-medium text-ink">{characteristic.value}</span>
    </span>
  );
  if (characteristic.typeUrl && characteristic.value) {
    return (
      <a
        href={`${characteristic.typeUrl}${characteristic.value}`}
        target="_blank"
        rel="noopener noreferrer"
      >
        {inner}
      </a>
    );
  }
  return inner;
}

function PersonHero({ person, canEdit }: { person: Person; canEdit: boolean }) {
  const color = promoColor(person.startYear);
  return (
    <div className="relative mb-5 overflow-hidden rounded-2xl border border-line bg-surface p-8">
      <div
        className="pointer-events-none absolute inset-0"
        style={{ background: `linear-gradient(135deg, ${color}12 0%, transparent 55%)` }}
      />
      <div className="relative flex flex-wrap items-start gap-7">
        {/* Avatar */}
        <div
          className="shrink-0 overflow-hidden rounded-[18px]"
          style={{
            width: 140,
            height: 140,
            boxShadow: `0 0 0 3px ${color}, 0 12px 32px ${color}30`,
          }}
        >
          <Avatar person={person} fill />
        </div>

        {/* Infos */}
        <div className="min-w-[240px] flex-1">
          <div
            className="mb-3 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[12px] font-medium"
            style={{ background: `${color}18`, color }}
          >
            <span className="h-1.5 w-1.5 rounded-full" style={{ background: color }} />
            Promo {person.startYear}
          </div>
          <h1 className="mb-4 text-[34px] font-semibold leading-tight tracking-tight text-ink">
            {person.firstName} <span className="text-ink-2">{person.lastName}</span>
          </h1>
          {person.description && (
            <p className="mb-3 max-w-[540px] text-[14px] leading-relaxed text-ink-2">
              {person.description}
            </p>
          )}
          {person.biography && (
            <p className="max-w-[540px] text-[13.5px] italic leading-relaxed text-ink-3">
              {person.biography}
            </p>
          )}
        </div>

        {/* Actions */}
        {canEdit && (
          <div className="flex flex-col gap-2">
            <Link
              to={`/person/${person.id}/edit`}
              className="inline-flex h-9 items-center justify-center rounded-[9px] bg-ink px-4 text-sm font-medium text-white transition-all hover:-translate-y-0.5 hover:opacity-90"
            >
              Modifier
            </Link>
          </div>
        )}
      </div>
    </div>
  );
}

function PersonPageSkeleton() {
  return (
    <div className="mx-auto max-w-[980px] px-7 py-7">
      <Skeleton className="mb-6 h-4 w-48" />
      <Skeleton className="mb-5 h-52 w-full rounded-2xl" />
      <div className="mb-5 grid grid-cols-3 gap-3">
        {[0, 1, 2].map((i) => (
          <Skeleton key={i} className="h-20 rounded-xl" />
        ))}
      </div>
      <Skeleton className="h-72 w-full rounded-xl" />
    </div>
  );
}

// ── Page ─────────────────────────────────────────────────────────────────────

export function PersonPage() {
  const { id } = useParams<{ id: string }>();
  const { user } = useAuth();
  const navigate = useNavigate();
  const [person, setPerson] = useState<Person | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(false);

  useEffect(() => {
    if (!id) return;
    void getPerson(Number(id)).then((result) => {
      if (result.ok) setPerson(result.data);
      else setError(true);
      setLoading(false);
    });
  }, [id]);

  if (loading) return <PersonPageSkeleton />;
  if (error || !person) {
    return (
      <div className="flex min-h-[40vh] items-center justify-center text-[14px] text-ink-3">
        Personne introuvable.
      </div>
    );
  }

  const color = promoColor(person.startYear);
  const canEdit = user !== null && (user.isAdmin || user.person.id === person.id);
  const visibleCharacteristics = person.characteristics.filter((c) => c.visible && c.value);
  const allSponsors = [...person.godFathers, ...person.godChildren];

  return (
    <div className="min-h-[calc(100vh-var(--header-height))] bg-bg">
      <div className="mx-auto max-w-[980px] px-7 pb-20 pt-7">
        <Breadcrumb
          items={[
            { label: 'Annuaire', onClick: () => void navigate('/tree') },
            { label: person.fullName },
          ]}
          className="mb-6"
        />

        <PersonHero person={person} canEdit={canEdit} />

        {/* Stats */}
        <div className="mb-5 grid grid-cols-3 gap-3">
          <StatCard label="Parrains" value={person.godFathers.length} accent={color} />
          <StatCard label="Fillots" value={person.godChildren.length} accent={color} />
          <StatCard label="Promo" value={person.startYear} />
        </div>

        {/* Arbre familial */}
        {allSponsors.length > 0 && (
          <Card radius="xl" padding="md" className="mb-5">
            <h2 className="mb-4 text-[17px] font-semibold tracking-tight text-ink">
              Arbre familial
            </h2>
            <FamilyGraph key={person.id} person={person} />
          </Card>
        )}

        {/* Parrainages (liste) */}
        {allSponsors.length > 0 && (
          <Card radius="xl" padding="md" className="mb-5">
            <h2 className="mb-4 text-[17px] font-semibold tracking-tight text-ink">Parrainages</h2>
            {person.godFathers.length > 0 && (
              <div className="mb-4">
                <p className="mb-2 text-[11px] font-semibold uppercase tracking-widest text-ink-3">
                  Parrains
                </p>
                <div className="grid gap-2 sm:grid-cols-2">
                  {person.godFathers.map((s) => (
                    <FamilyChip key={s.id} sponsor={s} personId={person.id} />
                  ))}
                </div>
              </div>
            )}
            {person.godChildren.length > 0 && (
              <div>
                <p className="mb-2 text-[11px] font-semibold uppercase tracking-widest text-ink-3">
                  Fillots
                </p>
                <div className="grid gap-2 sm:grid-cols-2">
                  {person.godChildren.map((s) => (
                    <FamilyChip key={s.id} sponsor={s} personId={person.id} />
                  ))}
                </div>
              </div>
            )}
          </Card>
        )}

        {/* Caractéristiques */}
        {visibleCharacteristics.length > 0 && (
          <Card radius="xl" padding="md">
            <h2 className="mb-3 text-[17px] font-semibold tracking-tight text-ink">Liens</h2>
            <div className="flex flex-wrap gap-2">
              {visibleCharacteristics.map((c) => (
                <CharacteristicChip key={c.id} characteristic={c} />
              ))}
            </div>
          </Card>
        )}
      </div>
    </div>
  );
}
