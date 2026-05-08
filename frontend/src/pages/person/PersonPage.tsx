import { useQuery } from '@tanstack/react-query';
import { Link, useNavigate, useParams } from 'react-router';
import { Avatar, Breadcrumb, Card, Skeleton, StatCard } from '../../components/ui';
import { useAuth } from '../../hooks/useAuth';
import { personQueries } from '../../lib/queries';
import { promoColor } from '../../lib/colors';
import type { Characteristic, Person } from '../../types/person';
import { FamilyGraph } from './FamilyGraph';

// ── Sub-components ────────────────────────────────────────────────────────────

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
            <span className="capitalize">{person.firstName.toLowerCase()}</span>{' '}
            <span className="capitalize text-ink-2">{person.lastName.toLowerCase()}</span>
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

  const {
    data: person,
    isLoading,
    isError,
  } = useQuery({
    ...personQueries.detail(Number(id)),
    enabled: !!id,
  });

  if (isLoading) return <PersonPageSkeleton />;
  if (isError || !person) {
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
