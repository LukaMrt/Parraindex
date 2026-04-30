import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router';
import { useAuth } from '../../hooks/useAuth';
import { getSponsor } from '../../lib/api/sponsors';
import type { Sponsor } from '../../types/sponsor';

const TYPE_LABELS: Record<string, string> = {
  HEART: 'Parrainage de cœur',
  CLASSIC: 'Parrainage classique',
  UNKNOWN: 'Lien inconnu',
};

function formatDate(dateStr: string | null): string {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
}

export function SponsorPage() {
  const { id } = useParams<{ id: string }>();
  const { user } = useAuth();
  const [sponsor, setSponsor] = useState<Sponsor | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(false);

  useEffect(() => {
    if (!id) return;
    void getSponsor(Number(id)).then((result) => {
      if (result.ok) setSponsor(result.data);
      else setError(true);
      setLoading(false);
    });
  }, [id]);

  if (loading) return <div className="p-8 text-medium-blue">Chargement…</div>;
  if (error || !sponsor) return <div className="p-8 text-medium-red">Parrainage introuvable.</div>;

  const canEdit =
    user !== null &&
    (user.isAdmin ||
      user.person.id === sponsor.godFatherId ||
      user.person.id === sponsor.godChildId);

  return (
    <div className="mx-auto max-w-3xl px-6 py-10">
      <h1 className="mb-8 text-center text-2xl font-bold text-dark-blue">
        {TYPE_LABELS[sponsor.type] ?? sponsor.type}
      </h1>

      {/* Les deux personnes */}
      <div className="flex items-center justify-center gap-8">
        <Link
          to={`/personne/${sponsor.godFatherId}`}
          className="flex flex-col items-center gap-2 text-center"
        >
          <div className="h-20 w-20 overflow-hidden rounded-full bg-medium-grey" />
          <span className="text-sm font-semibold text-dark-blue">{sponsor.godFatherName}</span>
          <span className="text-xs text-medium-blue">Parrain</span>
        </Link>

        <div className="flex flex-col items-center gap-1">
          {sponsor.date && <p className="text-sm text-medium-blue">{formatDate(sponsor.date)}</p>}
          <div className="h-0.5 w-16 bg-medium-grey" />
        </div>

        <Link
          to={`/personne/${sponsor.godChildId}`}
          className="flex flex-col items-center gap-2 text-center"
        >
          <div className="h-20 w-20 overflow-hidden rounded-full bg-medium-grey" />
          <span className="text-sm font-semibold text-dark-blue">{sponsor.godChildName}</span>
          <span className="text-xs text-medium-blue">Fillot</span>
        </Link>
      </div>

      {/* Description */}
      {sponsor.description && (
        <p className="mt-8 text-center text-medium-blue">{sponsor.description}</p>
      )}

      {/* Modifier */}
      {canEdit && (
        <div className="mt-8 flex justify-center">
          <Link
            to={`/parrainage/${sponsor.id}/modifier`}
            className="rounded bg-dark-blue px-5 py-2 text-sm text-white hover:bg-medium-blue"
          >
            Modifier ce lien
          </Link>
        </div>
      )}
    </div>
  );
}
